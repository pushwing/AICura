<?php

namespace App\Controllers\Admin;

use App\Models\GuideModel;
use App\Services\GuideService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * Admin 시술 가이드 관리 (이슈 #146)
 *
 * 가이드 CRUD. 본문은 Tiptap 에디터, FAQ 는 반복 입력으로 받아 GuideService 가 정규화한다.
 */
class GuideController extends BaseAdminController
{
    private GuideService $guideService;

    private const PER_PAGE = 20;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->guideService = Services::guideService();
    }

    public function index(): string
    {
        $result = $this->guideService->adminList([
            'status'  => (string) ($this->request->getGet('status') ?? ''),
            'keyword' => (string) ($this->request->getGet('keyword') ?? ''),
            'page'    => max(1, (int) ($this->request->getGet('page') ?? 1)),
            'limit'   => self::PER_PAGE,
        ]);

        return $this->render('admin/guides/index', [
            'guides' => $result['list'],
            'total'  => $result['total'],
        ]);
    }

    public function newForm(): string
    {
        return $this->render('admin/guides/form', ['guide' => null]);
    }

    public function edit(int $id): string
    {
        $guide = $this->guideService->find($id);
        if ($guide === null) {
            throw PageNotFoundException::forPageNotFound('가이드를 찾을 수 없습니다.');
        }

        return $this->render('admin/guides/form', ['guide' => $guide]);
    }

    public function create(): ResponseInterface
    {
        if (!$this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->guideService->create($this->formInput());

        return redirect()->to('/admin/guides')->with('success', '가이드가 등록되었습니다.');
    }

    public function update(int $id): ResponseInterface
    {
        if ($this->guideService->find($id) === null) {
            throw PageNotFoundException::forPageNotFound('가이드를 찾을 수 없습니다.');
        }
        if (!$this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->guideService->update($id, $this->formInput());

        return redirect()->to('/admin/guides')->with('success', '가이드가 수정되었습니다.');
    }

    public function delete(int $id): ResponseInterface
    {
        if ($this->guideService->find($id) === null) {
            throw PageNotFoundException::forPageNotFound('가이드를 찾을 수 없습니다.');
        }

        $this->guideService->delete($id);

        return redirect()->to('/admin/guides')->with('success', '가이드가 삭제되었습니다.');
    }

    /**
     * 폼 입력 수집 — 본문·FAQ 배열 포함.
     *
     * @return array<string, mixed>
     */
    private function formInput(): array
    {
        return [
            'title'          => (string) $this->request->getPost('title'),
            'slug'           => (string) ($this->request->getPost('slug') ?? ''),
            'summary'        => (string) ($this->request->getPost('summary') ?? ''),
            'content'        => (string) ($this->request->getPost('content') ?? ''),
            'procedure_name' => (string) ($this->request->getPost('procedure_name') ?? ''),
            'status'         => (string) ($this->request->getPost('status') ?? GuideModel::STATUS_DRAFT),
            'faq_q'          => $this->request->getPost('faq_q'),
            'faq_a'          => $this->request->getPost('faq_a'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function rules(): array
    {
        return [
            'title'  => 'required|max_length[200]',
            'status' => 'permit_empty|in_list[draft,published]',
        ];
    }
}
