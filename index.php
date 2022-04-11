<?php
require_once __DIR__ . '/vendor/autoload.php';
use Spatie\SimpleExcel\SimpleExcelReader;
use Spatie\SimpleExcel\SimpleExcelWriter;

// header("Content-type:text/html;charset=gbk");//iMC 导出表格为 GBK 编码，Excel 需要中文编码为 UTF-8
$pathToCsv = "upload/iMC_Exported_Table.csv";
// $path = $request->file('file')->storeAs('upload/', 'temp.csv');
// $pathToCsv = storage_path('app/').$path;

function getWeeklyAlarmDetails($pathOfOriginalTable) {
    $alarmDetails = []; //初始化中断情况统计表结果数组
    $counter = 0; //初始化序号计数器
    $rows = SimpleExcelReader::create($pathOfOriginalTable)->trimHeaderRow()->getRows(); //取得 iMC 导出记录所有条目
    foreach ($rows as $key => $value) { //对每个条目处理
        $counter++; //序号累加
        //中断地区
        $alarmSrc = $value[mb_convert_encoding('告警来源', "gbk") ];
        $pattern = '/_/'; //通过"_"分割字符串，[0] => 宿迁 [1] => 航道 [2] => MSR30(10.11.253.10)
        $alarmSrcSplitted = preg_split($pattern, $alarmSrc);
        //影响范围、站点A、站点B
        if ($alarmSrcSplitted[1] == mb_convert_encoding('市局', "gbk")) {
            $affectedArea = $alarmSrcSplitted[0] . mb_convert_encoding('市域', "gbk");
            $siteA = mb_convert_encoding('省厅', "gbk");
            $siteB = $alarmSrcSplitted[0] . $alarmSrcSplitted[1];
        } else {
            $affectedArea = $alarmSrcSplitted[0] . $alarmSrcSplitted[1];
            $siteA = $alarmSrcSplitted[0] . mb_convert_encoding('市局', "gbk");
            $siteB = $alarmSrcSplitted[0] . $alarmSrcSplitted[1];
        }
        $tempArray = array('序号' => $counter, '中断区域' => iconv('gbk', 'UTF-8', $alarmSrcSplitted[0]), '影响范围' => iconv('gbk', 'UTF-8', $affectedArea), '站点A' => iconv('gbk', 'UTF-8', $siteA), '站点B' => iconv('gbk', 'UTF-8', $siteB), '中断开始时间' => iconv('gbk', 'UTF-8', $value[mb_convert_encoding('告警时间', "gbk") ]), '中断结束时间' => iconv('gbk', 'UTF-8', $value[mb_convert_encoding('恢复时间', "gbk") ]), '中断累计时长' => iconv('gbk', 'UTF-8', $value[mb_convert_encoding('持续时间', "gbk") ]), '故障分类' => '待查', '故障原因' => '待查');
        array_push($alarmDetails, $tempArray); //将每个条目重整后压入最终表格的数组中
        
    }
    $writer = SimpleExcelWriter::streamDownload('WeeklyAlarmDetails.xlsx')->addRows($alarmDetails)->toBrowser();
}

function getDowntimeDetails($pathOfOriginalTable) {
    $downtimeDetails = [];
    $counter = 0; //初始化序号计数器
    $rows = SimpleExcelReader::create($pathOfOriginalTable)->trimHeaderRow()->getRows(); //取得 iMC 导出记录所有条目
    foreach ($rows as $key => $value) { //对每个条目处理
        $counter++; //序号累加
        //中断地区
        $alarmSrc = $value[mb_convert_encoding('告警来源', "gbk") ];
        $pattern = '/_/'; //通过"_"分割字符串，[0] => 宿迁 [1] => 航道 [2] => MSR30(10.11.253.10)
        $alarmSrcSplitted = preg_split($pattern, $alarmSrc);
        $downtimeChn = iconv('gbk', 'UTF-8', $value[mb_convert_encoding('持续时间', "gbk") ]);
        // $downtimeChn = str_replace(' ','',$downtimeChn);//删除空格
        $tempArray = array('序号' => $counter, '中断区域' => iconv('gbk', 'UTF-8', $alarmSrcSplitted[0]), '中断累计时长' => convertChnTimeToStd($downtimeChn));
        array_push($downtimeDetails, $tempArray); //将每个条目重整后压入最终表格的数组中
        
    }
    $writer = SimpleExcelWriter::streamDownload('WeeklyDowntime.xlsx')->addRows($downtimeDetails)->toBrowser();
}

function convertChnTimeToStd($chnTime) {
    $chnTime = str_replace('天', ' Days', $chnTime);
    $chnTime = str_replace('小时', ' Hours', $chnTime);
    $chnTime = str_replace('分钟', ' Minutes', $chnTime);
    $chnTime = str_replace('秒', ' Seconds', $chnTime);
    $stdDowntime = ((gmstrftime('%d', strtotime($chnTime) - strtotime("now")) - 1) * 24 + gmstrftime('%H', strtotime($chnTime) - strtotime("now"))) . ':' . gmstrftime('%M:%S', strtotime($chnTime) - strtotime("now"));
    // $stdDowntime = gmstrftime('%Y/%m/%d  %H:%M:%S', strtotime($chnTime) - strtotime("now") - 2208988800);
    return $stdDowntime;
}

function fixImcCsvFile($csvFile){
    $originalFileName = fopen($csvFile,"r");
    $result = fgets($originalFileName, 200);
    
    if(strpos($result, mb_convert_encoding('条记录。', "gbk"))){
        $tmpCsv = tempnam('/tmp','tmpFile');
        $fixedCsv = fopen($tmpCsv,'w');
        
        while(!feof($originalFileName)){
            fputs($fixedCsv,fgets($originalFileName));
        }
        
        fclose($fixedCsv);
        rename($tmpCsv,'upload/iMC_Exported_Table.csv');
    }
    else{
        // echo "not found";
    }
    fclose($originalFileName);
}

if (isset($_GET['action'])) {
    
    fixImcCsvFile($pathToCsv);
    
    switch ($_GET['action']) {
        case 'getAlarmTab':
            getWeeklyAlarmDetails($pathToCsv);
        break;
        
        case 'getDowntime':
            getDowntimeDetails($pathToCsv);
        break;
        
        default:
            echo '访问错误';
        break;
    }
}

?>
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

<body>
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

          <div class="navbar-collapse" id="">
            <div class="custom_menu-btn">
              <button onclick="openNav()">
                <span class="s-1"> </span>
                <span class="s-3"> </span>
              </button>
            </div>
            <div id="myNav" class="overlay">
              <div class="overlay-content">
                <a href="#table">生成统计</a>
                <a href="#upload">上传告警</a>
              </div>
            </div>
          </div>
        </nav>
      </div>
    </header>
    <!-- end header section -->
    <!-- slider section -->
    <section class=" slider_section position-relative" id="table">
      <div class="container-fluid">
        <div class="row">
          <div class=" col-md-5 offset-md-1">
            <div class="detail-box">
              <h1>
                告警统计工具
              </h1>
              <p>
                本工具仅用于为交通专网每周运行周报生成统计表格。<br>请在下载表格前先行上传最新的告警信息文件。
              </p>

              <div class="btn-box">
                <a href="?action=getAlarmTab" class="btn-1">
                  中断情况统计表
                </a>
                <a href="?action=getDowntime" class="btn-2">
                  中断时长统计表
                </a>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
              <div class="number_box">
                <div>
                  <span>
                    01/
                  </span>
                </div>
                <ol class="carousel-indicators">
                  <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active">
                    01
                  </li>
                  <li data-target="#carouselExampleIndicators" data-slide-to="1">
                    02
                  </li>
                  <li data-target="#carouselExampleIndicators" data-slide-to="2">
                    03
                  </li>
                  <li data-target="#carouselExampleIndicators" data-slide-to="3">
                    04
                  </li>
                </ol>
              </div>
              <div class="carousel-inner">
                <div class="carousel-item active">
                  <div class="img-box">
                    <img src="images/slider-img.png" alt="" />
                  </div>
                </div>
                <div class="carousel-item">
                  <div class="img-box">
                    <img src="images/slider-img.png" alt="" />
                  </div>
                </div>
                <div class="carousel-item">
                  <div class="img-box">
                    <img src="images/slider-img.png" alt="" />
                  </div>
                </div>
                <div class="carousel-item">
                  <div class="img-box">
                    <img src="images/slider-img.png" alt="" />
                  </div>
                </div>
              </div>
              <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                <span class="sr-only">Previous</span>
              </a>
              <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                <span class="sr-only">Next</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- end slider section -->
  </div>

  <!-- feature section -->
  <section class="feature_section layout_padding" id="upload">
    <div class="container">
      <div class="heading_container">
        <h2>
          更新网络告警
        </h2>
      </div>
      <div class="feature_container layout_padding2">
        <div class="box b-1">
          <div class="img-box">
            <img src="images/f-1.png" alt="" />
          </div>
          <div class="detail-box">
            <h5>
              告警更新说明
            </h5>
            <p>
              为了确保每次统计的告警为最新状态，请在生成统计表前上传最新的告警文件。<br>
			  上传的文件仅支持纯文本的 CSV 文件。请不要修改该文件的格式和中文编码方式。
            </p>
          </div>
        </div>
        <div class="box b-2">
          <div class="img-box">
            <img src="images/f-3.png" alt="" />
          </div>
          <div class="detail-box">
            <h5>
              上传告警文件
            </h5>
            <p>
			<br>请确认上传的文件为 H3C iMC PLAT 7.1 导出的 CSV 格式的告警文件。
			<form action="upload.php" method="post" enctype="multipart/form-data">
				<label for="file">文件名：</label>
				<input type="file" name="file" id="file">
				<input type="submit" name="submit" value="提交">
			</form>
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- end feature section -->

  <!-- info section -->
  <section class="info_section ">
    <div class="container">
      <div class="info_container">
        <div class="row">

          <div class="col-md-9 col-lg-6">
            <h6>
              常用链接
            </h6>
            <div class="link_box">
              <ul>
                <li>
                  <a href="http://10.1.254.254:8080/imc/default.jsf" target="_blank">
                    H3C 智能网络管理中心
                  </a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- end info section -->

  <!-- footer section -->
  <footer class="container-fluid footer_section">
    <p>
      &copy; 2022 All Rights Reserved. Coded by
      <a href="https://github.com/RoyLaw/">Roy Law</a>
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