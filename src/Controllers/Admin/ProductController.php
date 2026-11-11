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
        $status = (string) $request->getQuery('status', '');
        $scope = (string) $request->getQuery('scope', 'mine');
        $ownerId = $scope === 'all' ? 0 : (int) Auth::id();

        $paginator = Paginator::fromRequest($request, $this->products->adminCount($search, $categoryId, $ownerId, $status), self::PER_PAGE);

        return $this->render('admin.products.index', [
            'title' => 'Nguyên vật liệu',
            'rows' => $this->products->adminList($search, $categoryId, $ownerId, $status, $paginator->getLimit(), $paginator->getOffset()),
            'paginator' => $paginator,
            'categories' => (new CategoryRepository())->allForSelect(),
            'search' => $search,
            'categoryId' => $categoryId,
            'status' => $status,
            'scope' => $scope,
            'adminId' => (int) Auth::id(),
        ], 'layouts.admin');
    }

    public function show(Request $request, string $id): Response
    {
        $product = $this->products->findById((int) $id);
        if ($product === null) {
            return ErrorHandler::render(404, $request);
        }

        return $this->render('admin.products.show', [
            'title' => $product['name'],
            'product' => $product,
            'images' => $this->products->images((int) $id),
            'tags' => $this->products->tags((int) $id),
            'canModify' => ProductPolicy::canModify(Auth::user(), $product),
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
        if (!ProductPolicy::canModify(Auth::user(), $product)) {
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
        if (!ProductPolicy::canModify(Auth::user(), $product)) {
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
        if (!ProductPolicy::canModify(Auth::user(), $product)) {
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
        if (!ProductPolicy::canModify(Auth::user(), $product)) {
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
            'title' => $product ? 'Sửa nguyên vật liệu' : 'Thêm nguyên vật liệu',
            'product' => $product,
            'images' => $product ? $this->products->images((int) $product['id']) : [],
            'selectedTags' => $product ? $this->products->tagIds((int) $product['id']) : [],
            'categories' => (new CategoryRepository())->allForSelect(),
            'brands' => (new SimpleTableRepository('brands', []))->all(),
            'tags' => (new SimpleTableRepository('tags', []))->all(),
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
        $tagIds = array_map('intval', (array) ($input['tag_ids'] ?? []));

        if ($existing) {
            $productId = (int) $existing['id'];
            $this->products->update($productId, $data, $tagIds);
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
            $data['created_by'] = (int) Auth::id();
            $productId = $this->products->create($data, $tagIds);
            $this->flash('success', 'Đã thêm sản phẩm mới.');
        }

        foreach ($gallery as $path) {
            $this->products->addImage($productId, $path);
        }

        return $this->redirect('/admin/products');
    }
}
