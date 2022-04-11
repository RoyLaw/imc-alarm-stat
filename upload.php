<!DOCTYPE html>
<html>

<head>
  <!-- Basic -->
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <!-- Mobile Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <!-- Site Metas -->
  <meta name="keywords" content="" />
  <meta name="description" content="" />
  <meta name="author" content="" />

  <title>周报统计工具</title>

  <!-- bootstrap core css -->
  <link rel="stylesheet" type="text/css" href="css/bootstrap.css" />

  <!-- fonts style -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700|Poppins:400,700|Raleway:400,700&display=swap" rel="stylesheet" />

  <!-- Custom styles for this template -->
  <link href="css/style.css" rel="stylesheet" />
  <!-- responsive style -->
  <link href="css/responsive.css" rel="stylesheet" />
</head>

<body class="sub_page">
  <div class="hero_area">
    <!-- header section strats -->
    <header class="header_section">
      <div class="container-fluid">
        <nav class="navbar navbar-expand-lg custom_nav-container">
          <a class="navbar-brand" href="">
            <img src="images/logo.png" alt="" />
            <span>
              交通专网运行情况周报
            </span>
          </a>


        </nav>
      </div>
    </header>
    <!-- end header section -->
  </div>



  <!-- about section -->
  <section class="about_section layout_padding">
    <div class="container">
      <div class="row">
        <div class="col-md-6">
          <div class="img-box">
            <img src="images/about-img.png" alt="" />
            <div class="play_btn">
              <a href="">
                <img src="images/play-btn.png" alt="" />
              </a>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="detail-box">
            <div class="heading_container">
              <h2>
                上传
              </h2>
            </div>
            <p>

<?php
// 允许上传的图片后缀
$allowedExts = array("csv");

if(isset($_FILES["file"])){
    $temp = explode(".", $_FILES["file"]["name"]);
    // echo $_FILES["file"]["size"];
    $extension = end($temp);     // 获取文件后缀名
    
    if ((($_FILES["file"]["type"] == "application/vnd.ms-excel")
    || ($_FILES["file"]["type"] == "text/plain")
    || ($_FILES["file"]["type"] == "text/csv")
    || ($_FILES["file"]["type"] == "text/tsv")
    && ($_FILES["file"]["size"] < 2048000)   // 小于 2000 kb
    && in_array($extension, $allowedExts)))
    {
        if ($_FILES["file"]["error"] > 0)
        {
            echo "错误：: " . $_FILES["file"]["error"] . "<br>";
        }
        else
        {
            echo "上传文件名: " . $_FILES["file"]["name"] . "<br>";
            echo "文件类型: " . $_FILES["file"]["type"] . "<br>";
            echo "文件大小: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
            echo "文件临时存储的位置: " . $_FILES["file"]["tmp_name"] . "<br>";
        
        // 判断当前目录下的 upload 目录是否存在该文件
        // 如果没有 upload 目录，你需要创建它，upload 目录权限为 777
        
            // 如果 upload 目录不存在该文件则将文件上传到 upload 目录下
            move_uploaded_file($_FILES["file"]["tmp_name"], "upload/" . 'iMC_Exported_Table.csv');
            echo "文件存储在: " . "upload/" . 'iMC_Exported_Table.csv';
        
        }
    }
    else
    {
        echo "非法的文件格式！";
    }

}else{
    echo "未上传文件。";
}

?>

            </p>
            <a href="index.php#table">
              返回首页
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- end about section -->




  <!-- footer section -->
  <footer class="container-fluid footer_section">
    <p>
      &copy; 2022 All Rights Reserved. Coded by
      <a href="https://github.com/RoyLaw/" target="_blank">Roy Law</a>
    </p>
  </footer>
  <!-- footer section -->

  <script type="text/javascript" src="js/jquery-3.4.1.min.js"></script>
  <script type="text/javascript" src="js/bootstrap.js"></script>

  <script>
    function openNav() {
      document.getElementById("myNav").classList.toggle("menu_width");
      document
        .querySelector(".custom_menu-btn")
        .classList.toggle("menu_btn-style");
    }
  </script>


</body>

</html>