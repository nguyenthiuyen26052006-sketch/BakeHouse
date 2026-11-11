<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\ErrorHandler;
use App\Core\FileUploader;
use App\Core\Paginator;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Repositories\SimpleTableRepository;
use PDOException;
use RuntimeException;

/**
 * Config-driven CRUD (list + search + pagination + create/edit/delete + image upload)
 * for shared lookup resources: categories, brands, tags, banners.
 */
abstract class AbstractResourceController extends BaseController
{
    private const PER_PAGE = 10;

    /**
     * @return array{
     *   title: string, route: string, table: string, search: array<int, string>, upload_dir?: string,
     *   slug_from?: string, columns: array<int, array<string, string>>, fields: array<int, array<string, mixed>>
     * }
     */
    abstract protected function config(): array;

    private function repo(): SimpleTableRepository
    {
        $config = $this->config();

        return new SimpleTableRepository($config['table'], $config['search']);
    }

    private function base(): string
    {
        return '/admin/' . $this->config()['route'];
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->getQuery('q', ''));
        $repo = $this->repo();
        $paginator = Paginator::fromRequest($request, $repo->countAll($search), self::PER_PAGE);

        return $this->render('admin.resource.index', [
            'title' => $this->config()['title'],
            'config' => $this->config(),
            'rows' => $repo->paginate($search, $paginator->getLimit(), $paginator->getOffset()),
            'paginator' => $paginator,
            'search' => $search,
        ], 'layouts.admin');
    }

    public function create(Request $request): Response
    {
        return $this->form(null);
    }

    public function edit(Request $request, string $id): Response
    {
        $row = $this->repo()->find((int) $id);

        return $row === null ? ErrorHandler::render(404, $request) : $this->form($row);
    }

    public function store(Request $request): Response
    {
        return $this->save($request, null);
    }

    public function update(Request $request, string $id): Response
    {
        $row = $this->repo()->find((int) $id);

        return $row === null ? ErrorHandler::render(404, $request) : $this->save($request, $row);
    }

    public function destroy(Request $request, string $id): Response
    {
        $repo = $this->repo();
        $row = $repo->find((int) $id);
        if ($row === null) {
            return ErrorHandler::render(404, $request);
        }

        try {
            $repo->delete((int) $id);
        } catch (PDOException) {
            $this->flash('danger', 'Không thể xóa vì mục này đang được sử dụng.');

            return $this->redirect($this->base());
        }

        $uploader = new FileUploader();
        foreach ($this->config()['fields'] as $field) {
            if ($field['type'] === 'image') {
                $uploader->delete($row[$field['name']] ?? null);
            }
        }
        $this->flash('success', 'Đã xóa thành công.');

        return $this->redirect($this->base());
    }

    private function form(?array $row): Response
    {
        return $this->render('admin.resource.form', [
            'title' => ($row ? 'Sửa ' : 'Thêm ') . mb_strtolower($this->config()['title']),
            'config' => $this->config(),
            'row' => $row,
            'action' => $row ? $this->base() . '/' . $row['id'] : $this->base(),
        ], 'layouts.admin');
    }

    private function save(Request $request, ?array $existing): Response
    {
        $config = $this->config();
        $repo = $this->repo();
        $input = $request->getAllInput();
        $formUrl = $existing ? $this->base() . '/' . $existing['id'] . '/edit' : $this->base() . '/create';

        $rules = [];
        foreach ($config['fields'] as $field) {
            if (!empty($field['rules'])) {
                $rules[$field['name']] = $field['rules'];
            }
        }
        $validator = new Validator($input, $rules);
        $errors = $validator->validate() ? [] : $validator->errors();

        $data = [];
        $uploader = new FileUploader();
        $newImages = [];
        foreach ($config['fields'] as $field) {
            $name = $field['name'];
            switch ($field['type']) {
                case 'checkbox':
                    $data[$name] = isset($input[$name]) ? 1 : 0;
                    break;
                case 'image':
                    $file = $request->getFile($name);
                    $hasFile = $file !== null && $file['error'] !== UPLOAD_ERR_NO_FILE;
                    if ($hasFile) {
                        try {
                            $path = $uploader->upload($file, $config['upload_dir'] ?? $config['route']);
                            $data[$name] = $path;
                            $newImages[] = $path;
                        } catch (RuntimeException $e) {
                            $errors[$name] = $e->getMessage();
                        }
                    } elseif (!$existing && !empty($field['required_on_create'])) {
                        $errors[$name] = 'Vui lòng chọn ảnh.';
                    }
                    break;
                default:
                    $value = trim((string) ($input[$name] ?? ''));
                    $required = str_contains((string) ($field['rules'] ?? ''), 'required');
                    if (($field['format'] ?? '') === 'url' && $value !== '' && !preg_match('#^(/(?!/)|https?://)#i', $value)) {
                        $errors[$name] = 'Liên kết phải bắt đầu bằng "/" hoặc "http(s)://".';
                    }
                    $data[$name] = ($value === '' && !$required) ? null : $value;
            }
        }

        if ($errors !== []) {
            foreach ($newImages as $path) {
                $uploader->delete($path);
            }

            return $this->redirectWithErrors($request, $formUrl, $errors);
        }

        if (isset($config['slug_from'])) {
            $source = ($data['slug'] ?? null) ?: (string) $data[$config['slug_from']];
            $data['slug'] = $repo->slugFor($source, $existing ? (int) $existing['id'] : null);
        }

        if ($existing) {
            $repo->update((int) $existing['id'], $data);
            foreach ($config['fields'] as $field) {
                if ($field['type'] === 'image' && isset($data[$field['name']])) {
                    $uploader->delete($existing[$field['name']] ?? null);
                }
            }
            $this->flash('success', 'Đã cập nhật thành công.');
        } else {
            $repo->create($data);
            $this->flash('success', 'Đã thêm mới thành công.');
        }

        return $this->redirect($this->base());
    }
}
