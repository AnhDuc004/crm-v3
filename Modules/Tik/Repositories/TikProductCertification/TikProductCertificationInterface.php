<?php

namespace Modules\Tik\Repositories\TikProductCertification;

interface TikProductCertificationInterface
{
    public function getAll($queryData);
    public function findById($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function uploadFiles($id, $files = null, $images = null);
}
