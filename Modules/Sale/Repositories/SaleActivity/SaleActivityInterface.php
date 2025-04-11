<?php

namespace Modules\Sale\Repositories\SaleActivity;

interface SaleActivityInterface
{
    public function getSaleActivityByEstimate($id, $request);

    public function getSaleActivityByInvoice($id, $request);

    public function destroy($id);
}
