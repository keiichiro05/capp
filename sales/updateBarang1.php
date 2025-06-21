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
						$usersql = mysqli_query("SELECT * FROM pegawai where id_pegawai='$iduser'"); 
						$hasiluser=mysqli_fetch_array($usersql);
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
                        <li>
                            <a href="jualbarang.php">
                                <i class="fa fa-usd"></i> <span>Jual Barang</span>
                            </a>
                        </li>
                        <li>
                            <a href="datapenjualan.php">
                                <i class="fa fa-calendar"></i> <span>Data Penjualan</span>
                            </a>
                        </li>
                        <li  class="active">
                            <a href="updateBarang.php">
                                <i class="fa fa-suitcase"></i> <span>Harga Jual Barang</span>
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
                        Update Harga Jual Obat
                        <small>E-pharm</small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li><a href="index.php">Sales</a></li>
                        <li class="active">Update Harga Jual Obat</li>
                    </ol>
                </section>

<?php
$idBarang = $_GET['idBrg'];
$brg = mysqli_query("SELECT * FROM warehouse where id_barang='$idBarang'"); 
$hasilBrg=mysqli_fetch_array($brg);
?>
                <!-- Main content -->
                <section class="content">
                    <div class="row">
                    <div class="col-md-6">
                 
                    <div class="box box-primary">
                        <form role="form" method="POST" action="prosesUpdateHarga.php">
                        <input type="hidden" name="idbarang" value="<?php echo "".$idBarang ;?>" />
                                    <div class="box-body">
                                        <div class="form-group">
                                            <label>ID Barang : <?php echo "".$idBarang; ?></label>
                                           
                                        </div>
                                        <div class="form-group">
                                            <label>Nama Obat : <?php echo "".$hasilBrg['Nama']; ?></label>
                                        
                                         </div>
                                        <!-- <div class="form-group">
                                            <label>Harga</label>
                                            <input type="text" name="harga" class="form-control" value="1000" disabled/>
                                        </div> -->
                                        <div class="form-group">
                                            <label>Harga Jual Obat</label>
                                            <input type="text" name="hargajual" class="form-control" value="<?php echo "".$hasilBrg['Harga']; ?>"/>
                                        </div>
                                         <div class="box-footer">
                                        <button type="submit" class="btn btn-primary">Update</button>

                                    </div>
                                </div>
                            </form>
                            
                        </div>
                    </div>

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