<?php

namespace Modules\Customer\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Customer\Repositories\SpamFilter\SpamFilterInterface;

class SpamFilterController extends Controller
{
    const messageError = 'Bộ lọc thư rác không tồn tại';
    const errDelete = 'Xóa bộ lọc thư rác thất bại';


    protected $spamFilterRepository;

    public function __construct(SpamFilterInterface $spamFilterRepository)
    {
        $this->spamFilterRepository = $spamFilterRepository;
    }

    public function index(Request $request)
    {
        return Result::success($this->spamFilterRepository->listAll($request->all()));
    }

    public function store(Request $request)
    {
        try {
            $data = $this->spamFilterRepository->create($request->all());
            if (!$data) {
                return Result::fail(self::messageError);
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::messageError);
        }
    }

    public function show($id)
    {
        $data = $this->spamFilterRepository->findId($id);
        if (!$data) {
            return Result::fail(self::messageError);
        }
        return Result::success($data);
    }

    public function update($id, Request $request)
    {
        try {
            $data = $this->spamFilterRepository->update($id, $request->all());
            if (!$data) {
                return Result::fail(self::messageError);
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::messageError);
        }
    }

    public function destroy($id)
    {
        try {
            $data = $this->spamFilterRepository->destroy($id);
            if (!$data) {
                return Result::fail(self::errDelete);
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errDelete);
        }
    }
}
