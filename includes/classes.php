<?php

class KhachHang {
    public $maKhachHang;
    public $tencongty;
    public $ten;
    public $chucvu;
    public $sdt;
    public $diaChi;


    public function __construct($maKhachHang, $tencongty, $ten, $chucvu,  $sdt, $diaChi) {
        $this->maKhachHang = $maKhachHang ?: "KH" . time();
        $this->tencongty = $tencongty;
        $this->ten = $ten;
        $this->chucvu = $chucvu;
        $this->sdt = $sdt;
        $this->diaChi = $diaChi;
    }
}

class YeuCau {
    public $maYeuCau;
    public $tenYeuCau;
    public $noiDung;
    public $thoiGianGui;
    public $trangThai;
    public $thongTinKhachHang;
    public $maKhachHang;
    public $ghiChu;
    public $nguoiTiepNhan = null;

    public function __construct($tenYeuCau, $noiDung, KhachHang $khachHang) {
        $this->maYeuCau = self::generateRequestId();
        $this->tenYeuCau = $tenYeuCau;
        $this->noiDung = $noiDung;
        $this->thoiGianGui = date('Y-m-d H:i:s');
        $this->trangThai = "Mới tạo";

        $this->thongTinKhachHang = $khachHang;
        $this->maKhachHang = $khachHang->maKhachHang;
        $this->ghiChu = []; 
    }

    private static function generateRequestId() {
        $today = date('Ymd');
        $requests_file = 'data/requests.json';
        
        $existing_requests = [];
        if (file_exists($requests_file)) {
            $existing_requests = json_decode(file_get_contents($requests_file), true) ?: [];
        }
        
        $today_count = 0;
        foreach ($existing_requests as $request) {
            if (isset($request['maYeuCau']) && strpos($request['maYeuCau'], "YC{$today}") === 0) {
                $today_count++;
            }
        }
        
        $sequence = str_pad($today_count + 1, 3, '0', STR_PAD_LEFT);
        
        return "YC{$today}{$sequence}";
    }
}
?>