<?php 
use yii\helpers\Html;
use app\assets\WeiXinAsset;
WeiXinAsset::register($this);
$this->title = $title;
?>
<?php $this->beginPage()?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
  <meta charset="<?= Yii::$app->charset ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php echo  Html::csrfMetaTags() ?>
  <title><?php echo  Html::encode($this->title) ?></title>
  <!-- favorite icon starts -->
  <!-- <link rel="shortcut icon" href="images/common/favicon.ico" type="image/x-icon" /> -->
  <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody();?>
<!-- website wrapper starts -->
<div class="websiteWrapper"> 
  
  <!-- main menu wrapper starts -->
  <ul class="mainMenuWrapper">
    <li><a href="index.html">Landing Page</a></li>
    <li><a href="home.html">Home</a></li>
    <li><a href="typography.html">About</a> </li>
    <li><a href="faq.html">FAQ Page</a></li>
    <li><a href="404.html">404 Page</a></li>
    <li><a href="portfolioOneColumn.html">Portfolio One</a></li>
    <li><a href="portfolioTwoColumns.html">Portfolio Two</a></li>
    <li><a href="portfolioOneColumnFilterable.html">Filterable Portfolio One</a></li>
    <li><a href="portfolioTwoColumnsFilterable.html">Filterable Portfolio Two</a></li>
    <li><a href="singleProject.html">Single Portfolio Project</a></li>
    <li><a href="blog.html">Blog</a> </li>
    <li><a href="singlePost.html">Single Post</a></li>
    <li class="currentPage"><a href="contact.html">Contact</a></li>
  </ul>
  <!-- main menu wrapper ends --> 
  
  <!-- header wrapper starts -->
  <div class="headerOuterWrapper">
    <div class="headerWrapper"> <a href="#" class="mainMenuButton"><span>Menu</span></a></div>
  </div>
  <!-- header wrapper ends -->
  
  <!-- page wrapper starts -->
  <div class="pageWrapper contactPageWrapper"> 
    
    <!-- map starts -->
    <!-- <div class="contactMapWrapper">
      <h4 class="contactTitle mapTitle">We Are Here:</h4>
      <iframe src="" class="contactMap"></iframe>
    </div> -->
    <!-- map ends --> 
    
    <!-- social icons wrapper starts -->
    <!-- <div class="socialIconsWrapper"> <a href="#" class="socialIcon socialIconFacebookDark"></a> <a href="#" class="socialIcon socialIconRssDark"></a> <a href="#" class="socialIcon socialIconDribbbleDark"></a> <a href="#" class="socialIcon socialIconVimeoDark"></a> <a href="#" class="socialIcon socialIconTwitterDark"></a> <a href="#" class="socialIcon socialIconSkypeDark"></a> </div> -->
    <!-- social icons wrapper ends --> 
    
    <!-- contact form wrapper starts -->
    <div class="contactFormWrapper">
      
    </div>
    <!-- contact form wrapper ends --> 
    
  </div>
  <!-- page wrapper ends --> 
  
  <!-- footer wrapper starts -->
  <!-- <div class="footerWrapper">  -->
    <!-- footer logo starts --> 
    <!-- <a href="index.html" class="footerLogo"><img src="images/common/footerLogo.png" class="" alt=""/></a>  -->
    <!-- footer logo ends --> 
    <!-- footer social icons wrapper starts -->
    <!-- <div class="footerSocialIconsWrapper"> <a href="#" class="footerSocialIcon footerFacebookIcon"></a> <a href="#" class="footerSocialIcon footerTwitterIcon"></a> <a href="#" class="footerSocialIcon footerDribbbleIcon"></a> <a href="#" class="footerSocialIcon footerFlickrIcon"></a> <a href="#" class="footerSocialIcon footerVimeoIcon"></a> <a href="#" class="footerSocialIcon footerRssIcon"></a> </div> -->
    <!-- footer social icons wrapper ends --> 
  <!-- </div> -->
  <!-- footer wrapper ends --> 
  
</div>
<!-- website wrapper ends -->
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>