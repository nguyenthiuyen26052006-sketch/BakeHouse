<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Auth;
use App\Core\ErrorHandler;
use App\Core\FileUploader;
use App\Core\Paginator;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Policies\ProductPolicy;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;
use App\Repositories\SimpleTableRepository;
use App\Services\Chatbot\ProductEmbeddingIndexer;
use RuntimeException;

final class ProductController extends BaseController
{
    private const PER_PAGE = 10;

    public function __construct(private readonly ProductRepository $products = new ProductRepository())
    {
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->getQuery('q', ''));
        $categoryId = (int) $request->getQuery('category', 0);
        $view = (string) $request->getQuery('view', '');
        if (!in_array($view, ['active', 'restock', 'hidden'], true)) {
            $view = '';
        }

        $paginator = Paginator::fromRequest($request, $this->products->adminCount($search, $categoryId, $view), self::PER_PAGE);

        return $this->render('admin.products.index', [
            'title' => 'Sản phẩm',
            'rows' => $this->products->adminList($search, $categoryId, $view, $paginator->getLimit(), $paginator->getOffset()),
            'paginator' => $paginator,
            'categories' => (new CategoryRepository())->allForSelect(),
            'counts' => $this->products->viewCounts(),
            'search' => $search,
            'categoryId' => $categoryId,
            'view' => $view,
        ], 'layouts.admin');
    }

    public function create(Request $request): Response
    {
        return $this->form(null);
    }

    public function edit(Request $request, string $id): Response
    {
        $product = $this->products->findById((int) $id);
        if ($product === null) {
            return ErrorHandler::render(404, $request);
        }
        if (!ProductPolicy::canModify(Auth::user())) {
            return ErrorHandler::render(403, $request);
        }

        return $this->form($product);
    }

    public function store(Request $request): Response
    {
        return $this->save($request, null);
    }

    public function update(Request $request, string $id): Response
    {
        $product = $this->products->findById((int) $id);
        if ($product === null) {
            return ErrorHandler::render(404, $request);
        }
        if (!ProductPolicy::canModify(Auth::user())) {
            return ErrorHandler::render(403, $request);
        }

        return $this->save($request, $product);
    }

    public function quickUpdate(Request $request, string $id): Response
    {
        $product = $this->products->findById((int) $id);
        if ($product === null) {
            return ErrorHandler::render(404, $request);
        }
        if (!ProductPolicy::canModify(Auth::user())) {
            return ErrorHandler::render(403, $request);
        }

        $validator = new Validator($request->getAllInput(), ['price' => 'required|integer', 'stock_quantity' => 'required|integer']);
        $price = (int) $request->getInput('price');
        $stock = (int) $request->getInput('stock_quantity');
        if (!$validator->validate() || $price < 0 || $stock < 0) {
            $this->flash('danger', 'Giá và tồn kho phải là số nguyên không âm.');
        } else {
            $this->products->updatePriceAndStock((int) $id, $price, $stock);
            $this->flash('success', 'Đã cập nhật giá và tồn kho.');
        }

        return $this->redirectBack('/admin/products');
    }

    public function destroy(Request $request, string $id): Response
    {
        $product = $this->products->findById((int) $id);
        if ($product === null) {
            return ErrorHandler::render(404, $request);
        }
        if (!ProductPolicy::canModify(Auth::user())) {
            return ErrorHandler::render(403, $request);
        }

        if ($this->products->hasOrders((int) $id)) {
            $this->products->setActive((int) $id, false);
            $this->flash('warning', 'Sản phẩm đã có đơn hàng nên chỉ được ẩn, không xóa cứng.');

            return $this->redirect('/admin/products');
        }

        $uploader = new FileUploader();
        $uploader->delete($product['image_path']);
        foreach ($this->products->images((int) $id) as $image) {
            $uploader->delete($image['image_path']);
        }
        $this->products->delete((int) $id);
        $this->flash('success', 'Đã xóa sản phẩm.');

        return $this->redirect('/admin/products');
    }

    private function form(?array $product): Response
    {
        return $this->render('admin.products.form', [
            'title' => $product ? 'Sửa sản phẩm' : 'Thêm sản phẩm',
            'product' => $product,
            'images' => $product ? $this->products->images((int) $product['id']) : [],
            'categories' => (new CategoryRepository())->allForSelect(),
            'brands' => (new SimpleTableRepository('brands', []))->all(),
        ], 'layouts.admin');
    }

    private function save(Request $request, ?array $existing): Response
    {
        $input = $request->getAllInput();
        $formUrl = $existing ? '/admin/products/' . $existing['id'] . '/edit' : '/admin/products/create';

        $validator = new Validator($input, [
            'name' => 'required|max:190',
            'category_id' => 'required|integer',
            'price' => 'required|integer',
            'stock_quantity' => 'required|integer',
            'unit' => 'required|max:30',
            'description' => 'max:5000',
        ]);
        $errors = $validator->validate() ? [] : $validator->errors();
        if (!isset($errors['price']) && (int) $input['price'] < 0) {
            $errors['price'] = 'Giá không được âm.';
        }
        if (!isset($errors['stock_quantity']) && (int) $input['stock_quantity'] < 0) {
            $errors['stock_quantity'] = 'Tồn kho không được âm.';
        }

        $uploader = new FileUploader();
        $newPaths = [];
        $mainImage = null;
        $gallery = [];
        if ($errors === []) {
            try {
                $file = $request->getFile('image');
                if ($file !== null && $file['error'] !== UPLOAD_ERR_NO_FILE) {
                    $mainImage = $uploader->upload($file, 'products');
                    $newPaths[] = $mainImage;
                }
                foreach ($request->getFiles('gallery') as $galleryFile) {
                    $path = $uploader->upload($galleryFile, 'products');
                    $gallery[] = $path;
                    $newPaths[] = $path;
                }
            } catch (RuntimeException $e) {
                $errors['image'] = $e->getMessage();
            }
        }

        if ($errors !== []) {
            foreach ($newPaths as $path) {
                $uploader->delete($path);
            }

            return $this->redirectWithErrors($request, $formUrl, $errors);
        }

        $data = [
            'category_id' => (int) $input['category_id'],
            'brand_id' => ((int) ($input['brand_id'] ?? 0)) ?: null,
            'name' => trim((string) $input['name']),
            'description' => trim((string) ($input['description'] ?? '')) ?: null,
            'unit' => trim((string) $input['unit']),
            'price' => (int) $input['price'],
            'stock_quantity' => (int) $input['stock_quantity'],
            'is_featured' => isset($input['is_featured']) ? 1 : 0,
            'is_active' => isset($input['is_active']) ? 1 : 0,
        ];
        if ($mainImage !== null) {
            $data['image_path'] = $mainImage;
        }

        if ($existing) {
            $productId = (int) $existing['id'];
            $this->products->update($productId, $data);
            if ($mainImage !== null) {
                $uploader->delete($existing['image_path']);
            }
            foreach ((array) ($input['delete_images'] ?? []) as $imageId) {
                $image = $this->products->findImage((int) $imageId, $productId);
                if ($image !== null) {
                    $uploader->delete($image['image_path']);
                    $this->products->deleteImage((int) $imageId);
                }
            }
            $this->flash('success', 'Đã cập nhật sản phẩm.');
        } else {
            $data['slug'] = $this->products->slugFor($data['name'], null);
            $productId = $this->products->create($data);
            $this->flash('success', 'Đã thêm sản phẩm mới.');
        }

        foreach ($gallery as $path) {
            $this->products->addImage($productId, $path);
        }

        // Keep chatbot search fresh; a failure here must never block the admin (lazy sync covers new products).
        try {
            (new ProductEmbeddingIndexer())->sync($productId);
        } catch (\Throwable $e) {
            ErrorHandler::logException($e);
        }

        return $this->redirect('/admin/products');
    }
}
