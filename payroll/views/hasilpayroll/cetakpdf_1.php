<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
//Yii::import('application.extensions.MPDF57.*');
//include("mpdf.php");

$crow = count($modelDetail);
$num_per_page = 60;
$page = ceil($crow/$num_per_page);
$x=1;

$desc = 
$th =       "<tr><th>#</th>"
            . "<th>FT ID</th>"
            . "<th>No.Rek.Kredit</th>"
            . "<th>Nama Rekening</th>"
            . "<th>Tgl. Proses</th>"
            . "<th>No.Rek.Debet</th>"
            . "<th>Nominal</th>"
            . "<th>Status</th>"
            . "<th>Keterangan</th>"
        . "</tr>";

echo "
        <HTML>
        <HEAD>
        <TITLE>$nama_file</TITLE>
                
        <style>
        @page{size:A4 landscape; margin:5mm;}
        body{margin: 0px 0px 0px 0px; width:1020px; overflow-y: scroll;}
        table {border-collapse: collapse;}
        table td, table th{font:7pt Arial; padding:2px; }
        table th{font-weight:bold; vertical-align:center;}
        </style>
        </HEAD>";
	

echo "  <BODY margin='0'>
        <p style='font:10pt Arial; font-weight:bold'>
        <p style='font:14pt Arial; font-weight:bold'>HASIL PAYROLL<br><br>
	   <table style='border:0','width:100%','text-align:left'>
       
        <table style='width:60%'>
        <tr style= 'text-align:left'>
        <th>Nama File Upload :</th>
        <th style= 'text-align:right'>".$modelMaster['nama_file_upload']."</th>
        </tr>
        <tr style= 'text-align:left'>
        <th>Nama File Proses :</th>
        <th style= 'text-align:right'>".$modelMaster['nama_file_process']."</th>
        </tr>
        <tr style= 'text-align:left'>
        <th>Kode Parameter :</th>
        <th style= 'text-align:right'>".$modelMaster['kode_parameter']."</th>
        </tr>
        <tr style= 'text-align:left'>
        <th>No. Rek Debit :</th>
        <th style= 'text-align:right'>".$modelParam['acctno']."</th>
        </tr>
        <tr style= 'text-align:left'>
        <th>Cabang :</th>
        <th style= 'text-align:right'>".$modelMaster['co_code']."</th>
        </tr>
        <tr style= 'text-align:left'>
        <th>Tanggal Eksekusi :</th>
        <th style= 'text-align:right'>".$modelMaster['date_exec']."</th>
        </tr>
        <tr style= 'text-align:left'>
        <th>Jam Eksekusi :</th>
        <th style= 'text-align:right'>".$modelMaster['time_exec']."</th>
        </tr>
        <tr style= 'text-align:left'>
        <th>Inputter :</th>
        <th style= 'text-align:right'>".$modelMaster['inputter']."</th>
        </tr>
        <tr style= 'text-align:left'>
        <th>Authoriser :</th>
        <th style= 'text-align:right'>".$modelMaster['authoriser']."</th>
        </tr>
        </table>";
        
echo "
        <table style='width:60%'>
        <tr style= 'text-align:left'>
        <th>Total Data Berhasil    :</th>
        <th>".$summary['jumlah_transaksi_sukses']."</th>
        <th>Total Nominal Berhasil :</th>
        <th>Rp.".number_format($summary['jumlah_nominal_transaksi_sukses'],2)."</th>
        </tr>
        <tr style= 'text-align:left'>
            <th>Total Data Gagal       :</th>
            <th>".$summary['jumlah_transaksi_gagal']."</th>
            <th>Total Nominal Gagal    :</th>
            <th>Rp.".number_format($summary['jumlah_nominal_transaksi_gagal'],2)."</th>
        </tr>
        <tr style= 'text-align:left'>
            <th>Total Data             :</th>
            <th>".$nFile_pay." data</th>
            <th>Total Nominal Terdebet :</th>
            <th>Rp.".number_format($summary['jumlah_nominal_terdebit'],2)."</th>
        </tr>
        </table>";

echo "<br><br>";

echo "<table border=1 width=100%>";

echo $th;
$no = 1;
foreach($modelDetail as $data){
    echo "
            <tr>
            <td>".$data['id']."</td>
            <td>".$data['ft_id']."</td>
            <td>".$data['acctno_cr']."</td>
            <td>".$data['acct_title']."</td>
            <td>".$data['date_process']."</td>
            <td>".$data['acctno_db']."</td>
            <td>".number_format($data['payrollamt'],2)."</td>
            <td>".$data["ft_stat"]."</td>
            <td>".$data["ft_msg"]."</td>
            </tr>";
    if($no%$num_per_page == false && $num_per_page != $crow){
                            $x++;
    
    echo "</table>
            <div style='page-break-after:always'></div>
            </table>
            <table style='float:right'>
            <tr><td>Total Data</td><td>:</td><td>.$crow.</td></tr>
            <tr><td>Halaman</td><td>:</td><td>.$x.'/'.$page.</td></tr>
            </table>
            <div style='clear:both'></div>                
            <table border=1 width=100%>.$th";
    }
    $no++;
    }
echo "</table></BODY></HTML>";



//
//$mpdf=new mPDF('utf-8','A4',10,'Myriad Pro',20,15,10,18,10,5,'');
//$filename = 'Hasil Payroll-' .date('d-m-Y').'-'.$model->nama_file_upload;
//$mpdf->WriteHTML($html,2);
//
//$mpdf->Output();
//exit;
?>
