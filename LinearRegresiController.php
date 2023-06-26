<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class LinearRegresiController extends Controller
{
    public function index()
    {
        // $data =  DB::table('testing_transaksi')
        // ->select(DB::raw('YEAR(date) as year, MONTH(date) as month, COUNT(*) as qty'))
        // ->groupBy('year', 'month')
        // ->orderBy('year', 'asc')
        // ->orderBy('month', 'asc')
        // ->get();
        // $data =  DB::table('testing_transaksi')
        // ->select(DB::raw('YEAR(date) as year, MONTH(date) as month, DAY(date) as day, COUNT(*) as qty'))
        // ->groupBy('year', 'month','day')
        // ->orderBy('year', 'asc')
        // ->orderBy('month', 'asc')
        // ->orderBy('day', 'asc')
        // ->get();
        $data =  DB::table('testing_transaksi')
        ->select('date',DB::raw('COUNT(*) as qty'))
        ->groupBy('date')
        ->get();

        $x2 = [];
        $y2 = [];
        $xy = [];
        $y = [];
        $x = [];
        $x_new[] = 1;
        $n_old = [];
        $lop_x = 1;
        $lop_n_old = 1;
        $lop_x2=1;
        $lop_xy=1;
        foreach ($data as $d) {
            $y[] = $d->qty;
            $x[] = $lop_x++;
            $n_old[] = $lop_n_old++;
            $x2[] = pow($lop_x2++, 2);
            $y2[] = $d->qty * $d->qty;
            $xy[] = $d->qty * $lop_xy++;
        }
        $ttl_y = array_sum($y);
        $ttl_x = array_sum($x);
        $ttl_x2 = array_sum($x2);
        $ttl_y2 = array_sum($y2);
        $ttl_xy = array_sum($xy);
        $n = count($n_old);
        $a1 = ($ttl_y * $ttl_x2) - ($ttl_x * $ttl_xy);
        $a2 = $n * $ttl_x2 - pow($ttl_x, 2);
        $a = $a1 / $a2;

        $b1 = ($n * $ttl_xy) - ($ttl_x * $ttl_y);
        $b2 = $n * $ttl_x2 - pow($ttl_x, 2);
        $b = $b1 / $b2;
        $result_prediksi = round($a + $b * 13);//hasil prediksi bulan depan

        $yt = [];
        $abs_error = [];
        $mad = [];
        $mse =[];

        $lop_yt=1;
        $lop_yy=1;
        $lop_abs_error1=1;
        $lop_abs_error2=1;
        $lop_mse=1;
        $lop_mape=1;
        $lop_rmse=1;
        $rmse=[];
        foreach ($data as $dn) {
            $yt[] = $a + $b * $lop_yt++;//nilai prediksi
            $mad[] = abs($dn->qty - ($a + $b * $lop_yy++));//MAD
         //   $abs_error[] = abs($lop_abs_error1++ - $a + $b *  $lop_abs_error2++);
            $mse[] = pow(abs(($a + $b * $lop_mse++) - $dn->qty),2);
            $mape[] = abs((($dn->qty- ($a + $b * $lop_mape++))/$dn->qty)*100);
           // $rmse[]=pow(($dn->qty - ($a + $b * $lop_rmse++)),2);
        }

        $res_prediksi = array_sum($yt);//Total Prediksi
        $res_mad = array_sum($mad);//total MAD
        $res_mse = array_sum($mse);//Total MSE
        $res_rmse =sqrt($res_mse/$n);//Nilai RMSE
        $res_mape = array_sum($mape)/$n; // nilai


        $result_persentase_error = ($res_mape/100)*$result_prediksi;//berapa persen kemungkinan error
        $result_error = round($result_prediksi + $result_persentase_error); //perkiraan stok

        $res_fix_perkiraan_prediksi = 'Perkiraan penjualan bulan depan adalah '.$result_prediksi.' - '.$result_error;
       return view('Admin.linear-regresi.index',compact('data','result_prediksi','result_error','res_mape'));
    }
}


//     $data = DB::table('testing')
//     // ->select(DB::raw('sum(qty) as qty'),DB::raw('YEAR(date) as year, MONTH(date) as month'))
//     // ->groupBy('year','month')
//     ->get();

// $x2 = [];
// $y2 = [];
// $xy = [];
// $y = [];
// $x = [];
// $x_new[] = 1;
// $n_old = [];
// $no = 1;
// foreach ($data as $d) {
//     $y[] = $d->qty;
//     $x[] = $d->terjual;
//     $n_old[] = $d->terjual;
//     $x2[] = pow($d->terjual, 2);
//     $y2[] = $d->qty * $d->qty;
//     $xy[] = $d->qty * $d->terjual;
// }
// $ttl_y = array_sum($y);
// $ttl_x = array_sum($x);
// $ttl_x2 = array_sum($x2);
// $ttl_y2 = array_sum($y2);
// $ttl_xy = array_sum($xy);
// $n = count($n_old);
// $a1 = ($ttl_y * $ttl_x2) - ($ttl_x * $ttl_xy);
// $a2 = $n * $ttl_x2 - pow($ttl_x, 2);
// $a = $a1 / $a2;

// $b1 = ($n * $ttl_xy) - ($ttl_x * $ttl_y);
// $b2 = $n * $ttl_x2 - pow($ttl_x, 2);
// $b = $b1 / $b2;
// $y_fix = $a + $b * 13;

// $yt = [];
// $abs_error = [];
// $yy = [];
// $mse =[];

// foreach ($data as $dn) {
//     $yt[] = $a + $b * $dn->terjual;
//     $yy[] = abs($dn->qty - ($a + $b * $dn->terjual));
//     $abs_error[] = abs($dn->terjual - $a + $b * $dn->terjual);
//     $mse[] = pow(abs($dn->qty - ($a + $b * $dn->terjual)),2);
//     // $mape[] = abs((array_sum($yy) / $n) * 100);
// }

// $res_mse = array_sum($mse)/$n;
// $res_yy = array_sum($yy);
// $res_rmse = sqrt($res_mse);

// $jml1 = 1;
// $jml = $n;
// $atas = $jml1 * $res_yy;
// $bawah = $jml * $ttl_y;
// $res_mape = ($atas / $bawah) * 100; // persentase kemungkinan kesalahan


// // $data_new = array(
// //     'x' => $x,
// //     'y' => $y,
// //     'x2' => $x2,
// //     'y2' => $y2,
// //     'xy' => $xy,
// //     'y"' => $yt,
// //     'Y-Y"' => $yy,
// // );

// $result_prediksi = round($y_fix);
// $result_persentase_error = ($res_mape/100)*$result_prediksi;
// $fix_result_error = round($result_prediksi + $result_persentase_error);

// $res_fix_perkiraan_prediksi = 'Perkiraan penjualan bulan depan adalah '.$result_prediksi.' - '.$fix_result_error;
// return $res_fix_perkiraan_prediksi;
// }
