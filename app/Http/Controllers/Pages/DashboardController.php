<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller as ParentController;
use App\Models\User;
use Eod;
use Illuminate\Support\Facades\Cache;
// use RadicalLoop\Eod\Config;
// use RadicalLoop\Eod\Eod;
use Inertia\Inertia;

class DashboardController extends ParentController
{
    public function index()
    {
        return Inertia::render('Dashboard', [
            'stats'         => $this->getStats(),
            'livestocks'    => $this->getStocks(),
        ]);
    }

    public function getStats()
    {
        return Cache::remember('dashboard_stats', 60, function () {
            $totalMembers = User::count();
            $newMembersToday = User::whereDate('created_at', today())->count();
            $memberGrowth = $this->calculateGrowthPercentage(
                User::whereDate('created_at', '>=', now()->subDays(7))->count(),
                User::whereDate('created_at', '>=', now()->subDays(14))->whereDate('created_at', '<', now()->subDays(7))->count()
            );

            return [
                [
                    'title'  => 'Total Members',
                    'value'  => number_format($totalMembers),
                    'growth' => sprintf('%+.1f%%', $memberGrowth),
                ],
                [
                    'title'  => 'New Members Today',
                    'value'  => number_format($newMembersToday),
                    'growth' => sprintf('%+.1f%%', $newMembersToday > 0 ? 100 : 0),
                ],
                [
                    'title'  => 'Weekly Growth',
                    'value'  => sprintf('%+.1f%%', $memberGrowth),
                    'growth' => sprintf('%+.1f%%', $memberGrowth),
                ],
                [
                    'title'  => 'Total Sessions',
                    'value'  => number_format(rand(5000, 15000)),
                    'growth' => sprintf('%+.1f%%', rand(5, 15)),
                ],
            ];
        });
    }

    private function calculateGrowthPercentage($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return (($current - $previous) / $previous) * 100;
    }

    public function getStocks()
    {
        $data = [];
        // https://github.com/radicalloop/eodhistoricaldata
        // $exchanges = Eod::exchange();
        // $exchange->symbol('US')->json();
        // $exchange->multipleTicker('US')->json();
        // $exchange->details('US')->json();

        $stocks = Eod::stock();
        // $nvidia = $stocks->realTime('NVDA.US')->json();
        $apple = $stocks->realTime('AAPL.US')->json();
        $paypal = $stocks->realTime('PYPL.US')->json();
        $tesla = $stocks->realTime('TSLA.US')->json();
        $hpinc = $stocks->realTime('HPQ.US')->json();

        // $nvidiaDataObject = json_decode($nvidia);
        $appleDataObject = json_decode($apple);
        $paypalDataObject = json_decode($paypal);
        $teslaDataObject = json_decode($tesla);
        $hpincDataObject = json_decode($hpinc);

        // array_push($data, [
        //     'symbol'    => $nvidiaDataObject->code,
        //     'name'      => 'Nvidia',
        //     'price'     => $nvidiaDataObject->close,
        //     'change'    => $nvidiaDataObject->change,
        //     'icon'      => 'https://www.nvidia.com/en-us/about-nvidia/legal-info/logo-brand-usage/_jcr_content/root/responsivegrid/nv_container_392921705/nv_container/nv_image.coreimg.100.630.png/1703060329053/nvidia-logo-vert.png',
        //     'bgColor'   => 'gray',
        //     'currency'  => '$',
        // ]);

        array_push($data, [
            'symbol'    => $appleDataObject->code,
            'name'      => 'Apple',
            'price'     => $appleDataObject->close,
            'change'    => $appleDataObject->change,
            'icon'      => 'https://upload.wikimedia.org/wikipedia/commons/f/fa/Apple_logo_black.svg',
            'bgColor'   => 'gray',
            'currency'  => '$',
        ]);

        array_push($data, [
            'symbol'    => $paypalDataObject->code,
            'name'      => 'PayPal',
            'price'     => $paypalDataObject->close,
            'change'    => $paypalDataObject->change,
            'icon'      => 'https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg',
            'bgColor'   => 'blue',
            'currency'  => '$',
        ]);

        array_push($data, [
            'symbol'    => $teslaDataObject->code,
            'name'      => 'Tesla, Inc',
            'price'     => $teslaDataObject->close,
            'change'    => $teslaDataObject->change,
            'icon'      => 'https://upload.wikimedia.org/wikipedia/commons/e/e8/Tesla_logo.png',
            'bgColor'   => 'red',
            'currency'  => '$',
        ]);

        array_push($data, [
            'symbol'    => $hpincDataObject->code,
            'name'      => 'HP Inc',
            'price'     => $hpincDataObject->close,
            'change'    => $hpincDataObject->change,
            'icon'      => 'https://logodownload.org/wp-content/uploads/2014/04/hp-logo-1.png',
            'bgColor'   => 'blue',
            'currency'  => '$',
        ]);

        return $data;
    }
}
