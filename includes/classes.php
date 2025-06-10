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
        $this->maYeuCau = "YC" . time();
        $this->tenYeuCau = $tenYeuCau;
        $this->noiDung = $noiDung;
        $this->thoiGianGui = date('Y-m-d H:i:s');
        $this->trangThai = "Mới tạo";

        $this->thongTinKhachHang = $khachHang;
        $this->maKhachHang = $khachHang->maKhachHang;
        $this->ghiChu = []; 
    }
}
?>