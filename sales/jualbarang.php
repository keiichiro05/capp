<!DOCTYPE html>
<?php
include "konekdb.php";
session_start();
if(!isset($_SESSION['username'])){
	header ("location:../index.php");
}
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>E-pharm | Tambah Data Penjualan</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <!-- bootstrap 3.0.2 -->
        <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <!-- font Awesome -->
        <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <!-- Ionicons -->
        <link href="../css/ionicons.min.css" rel="stylesheet" type="text/css" />
        <!-- DATA TABLES -->
        <link href="../css/datatables/dataTables.bootstrap.css" rel="stylesheet" type="text/css" />
        <!-- Theme style -->
        <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />
    <link href="../css/modern-3d.css" rel="stylesheet" type="text/css" />

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
    </head>
    <body class="skin-blue">
        <!-- header logo: style can be found in header.less -->
        <header class="header">
            <a href="index.php" class="logo">
                <!-- Add the class icon to your logo image or logo icon to add the margining -->
                E-pharm
            </a>
            <!-- Header Navbar: style can be found in header.less -->
            <nav class="navbar navbar-static-top" role="navigation">
                <!-- Sidebar toggle button-->
                <a href="#" class="navbar-btn sidebar-toggle" data-toggle="offcanvas" role="button">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </a>
                <div class="navbar-right">
                    <ul class="nav navbar-nav">
                        <!-- Messages: style can be found in dropdown.less-->
                         <ul class="dropdown-menu">
                                <li class="footer">
                                    <a href="#">View all tasks</a>
                                </li>
                            </ul>
                        </li>
                        <?php 
						$username=$_SESSION['username'];
						$iduser=$_SESSION['idpegawai'];
                        $usersql = mysqli_query($mysqli, "SELECT * FROM pegawai WHERE id_pegawai='$iduser'"); 
                        $hasiluser = mysqli_fetch_array($usersql);
						?>
                        <!-- User Account: style can be found in dropdown.less -->
                        <li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="glyphicon glyphicon-user"></i>
                                <span><?php echo "$hasiluser[Nama]"; ?> <i class="caret"></i></span>
                            </a>
                            <ul class="dropdown-menu">
                                <!-- User image -->
                                <li class="user-header bg-light-blue">
                                    <img src="<?php echo "$hasiluser[foto]";?>" class="img-circle" alt="User Image" />
                                    <p>
                                        <?php echo $hasiluser['Nama']." - ".$hasiluser['Jabatan']; ?>
                                        <small>Member since <?php echo "$hasiluser[Tanggal_Masuk]"; ?></small>
                                    </p>
                                </li>
                                <!-- Menu Body -->
                                <li class="user-body">
                                    <div class="col-xs-4 text-center">
                                        <a href="#">Followers</a>
                                    </div>
                                    <div class="col-xs-4 text-center">
                                        <a href="#">Sales</a>
                                    </div>
                                    <div class="col-xs-4 text-center">
                                        <a href="#">Friends</a>
                                    </div>
                                </li>
                                <!-- Menu Footer-->
                                <li class="user-footer">
                                    <div class="pull-left">
                                        <a href="profil.php" class="btn btn-default btn-flat">Profile</a>
                                    </div>
                                    <div class="pull-right">
                                        <a href="prosesLogout.php" class="btn btn-default btn-flat">Sign out</a>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>
        <div class="wrapper row-offcanvas row-offcanvas-left">
            <!-- Left side column. contains the logo and sidebar -->
            <aside class="left-side sidebar-offcanvas">
                <!-- sidebar: style can be found in sidebar.less -->
                <section class="sidebar">
                    <!-- Sidebar user panel -->
                    <div class="user-panel">
                        <div class="pull-left image">
                            <img src="<?php echo "$hasiluser[foto]";?>" class="img-circle" alt="User Image" />
                        </div>
                        <div class="pull-left info">
                            <p>Hello, <?php echo "$hasiluser[Nama]"; ?></p>

                            <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                        </div>
                    </div>
                    <!-- search form -->
                   
                    <!-- /.search form -->
                    <!-- sidebar menu: : style can be found in sidebar.less -->
<ul class="sidebar-menu">

                       <li>
                            <a href="index.php">
                                <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                            </a>
                        </li>
                        <li  class="active">
                            <a href="jualbarang.php">
                                <i class="fa fa-usd"></i> <span>Jual Barang</span>
                            </a>
                        </li>
                        <li>
                            <a href="datapenjualan.php">
                                <i class="fa fa-calendar"></i> <span>Data Penjualan</span>
                            </a>
                        </li>
						<li>
                            <a href="cuti.php">
                                <i class="fa fa-suitcase"></i> <span>Cuti</span>
                            </a>
                        </li>
                       <li>
                            <a href="mailbox.php">
                                <i class="fa fa-comments"></i> <span>Mailbox</span>
								
                            </a>
                        </li>
                        
                         
                        
                    </ul>
                </section>
                <!-- /.sidebar -->
            </aside>





            <!-- Right side column. Contains the navbar and content of the page -->
            <aside class="right-side">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1>
                        Form Penjualan Obat
                        <small>E-pharm</small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li><a href="#">Sales</a></li>
                        <li class="active">Penjualan Obat</li>
                    </ol>
                </section>

<?php include "konekdb.php";
$sql = "SELECT id_pemasukan FROM pemasukan order by id_pemasukan DESC limit 1";
$id = mysqli_query($mysqli, $sql);
$idmasuk = mysqli_fetch_array($id);
$idmasuk1=$idmasuk['id_pemasukan'];
$idmasuk11=$idmasuk1+1;
 ?>

<datalist id="namaobat">
<?php
$obat = mysqli_query($mysqli, "SELECT Nama FROM warehouse");
while($obat1 = mysqli_fetch_array($obat)) {
echo "<option value=\"$obat1[Nama]\"></option>"; 
}
?>
</datalist>
                <!-- Main content -->
                <section class="content">
                    <div class="row">
                    <div class="col-md-6">
                    <?php if(isset($_GET['idpesan'])){ ?>
                    <?php if($_GET['idpesan'] == 1){ ?>
                 
                                
                                <div class="box-body">
                                <div class="alert alert-warning alert-dismissable">
                                        <i class="fa fa-warning"></i>
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                        <b>Alert!</b> <?php echo "".$_GET['pesan']; ?>
                                    </div>
                                </div>
                                
                    <?php } else{ ?>
                    
                     <div class="box-body">
					<div class="alert alert-success alert-dismissable">
                                        <i class="fa fa-check"></i>
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                        <b>Sukses!</b> <?php echo "".$_GET['pesan']; ?>
                                    </div>
                                    </div>
                                    
					<?php } }?>
                    <div class="box box-primary">
                        <form role="form" method="GET" action="postSales.php">
                                    <div class="box-body">
                                        <div class="form-group">
                                            <label>ID Pemasukan</label>
                                            <?php echo "<input type=\"text\" name=\"idpemasukan\" class=\"form-control\" value=".$idmasuk11." />"; ?>
                                        </div>
                                        <div class="form-group">
                                            <label>Nama Obat</label>
                                        <div class="input-group input-group-sm">
                                        <input list="namaobat" name="namaobat" placeholder="input nama obat" class="form-control">
                                        <span class="input-group-btn">
                                            <?php echo "<button class=\"btn btn-info btn-flat\" type=\"button\">Cek Obat</button>" ?>
                                        </span>
                                        </div>
                                         </div>
                                        <!-- <div class="form-group">
                                            <label>Harga</label>
                                            <input type="text" name="harga" class="form-control" value="1000" disabled/>
                                        </div> -->
                                        <div class="form-group">
                                            <label>Jumlah Obat</label>
                                            <input type="text" name="jumlahobat" class="form-control" placeholder="jumlah pembelian"/>
                                        </div>
                                         <div class="box-footer">
                                        <button type="submit" class="btn btn-primary">Submit</button>

                                    </div>
                                </div>
                            </form>
                            
                        </div>
                    </div>

                    </div>
                    <div class="row">
<?php 
echo "<div class=\"box\">
                                <div class=\"box-header\">
                                    <h3 class=\"box-title\">Detail Transaksi</h3>                                    
                                </div><!-- /.box-header -->
                                <div class=\"box-body table-responsive\">
                                    <table id=\"example1\" class=\"table table-bordered table-striped\">
                                        <thead>
                                            <tr>
                                                <th>Nama Obat</th>
                                                <th>Harga Satuan</th>
                                                <th>Jumlah</th>
                                                <th>Total</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>";

$sql1 = "SELECT * FROM penjualan where id_pemasukan='$idmasuk11'";
$tm = mysqli_query($mysqli, $sql1);
if(mysqli_num_rows($tm) > 0) {
   while ($tmpl = mysqli_fetch_array($tm)) {
   $sql2 = "SELECT * FROM warehouse WHERE id_barang='$tmpl[id_barang]'";
$tm2 = mysqli_query($mysqli, $sql2);
   $tmpl2 = mysqli_fetch_array($tm2);
   $idbrg=$tmpl['id_barang'];
   $nmobat1=$tmpl2['Nama'];
   $hrg1=$tmpl2['Harga'];
   $jml1=$tmpl['jumlah'];
   $ttl=$tmpl['total'];
   $idpenjualan=$tmpl['id_penjualan'];


                                            echo "<tr>
                                                <td>$nmobat1</td>
                                                <td>$hrg1</td>
                                                <td>$jml1</td>
                                                <td>$ttl</td>
                                                <td><a href=\"getHapusBarang.php?idjual=$idpenjualan&&jml=$jml1&&idbarang=$idbrg\"><button class=\"btn btn-danger\">Hapus</button></a></td>
                                            </tr>";
}
   
                                           
                                       
}else{
     $nmobat1="";
   $hrg1="";
   $jml1="";
   $ttl="";
   echo "<tr>
                                                <td>$nmobat1</td>
                                                <td>$hrg1</td>
                                                <td>$jml1</td>
                                                <td>$ttl</td>
                                                <td></td>
                                            </tr>";
}
echo " </tbody>
                                        <tfoot>
                                            <tr>
                                                <th>Nama Obat</th>
                                                <th>Harga Satuan</th>
                                                <th>Jumlah</th>
                                                <th>Total</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                    <a href=\"prosesJual.php?idmasuk=$idmasuk11\"><button class=\"btn btn-info\">Proses</button></a>
                                </div><!-- /.box-body -->
                            </div>";


?>

                            
                    </div>
                </section><!-- /.content -->
            </aside><!-- /.right-side -->
        </div><!-- ./wrapper -->
        <!-- jQuery 2.0.2 -->
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
        <!-- Bootstrap -->
        <script src="../js/bootstrap.min.js" type="text/javascript"></script>
        <!-- DATA TABES SCRIPT -->
        <script src="../js/plugins/datatables/jquery.dataTables.js" type="text/javascript"></script>
        <script src="../js/plugins/datatables/dataTables.bootstrap.js" type="text/javascript"></script>
        <!-- AdminLTE App -->
        <script src="../js/AdminLTE/app.js" type="text/javascript"></script>
    <script type="text/javascript">
            $(function() {
                $("#example1").dataTable();
                $('#example2').dataTable({
                    "bPaginate": true,
                    "bLengthChange": false,
                    "bFilter": false,
                    "bSort": true,
                    "bInfo": true,
                    "bAutoWidth": false
                });
            });
        </script>
    </body>
    
</html>