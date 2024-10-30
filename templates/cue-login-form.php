<!-- CUE LOGIN FORM -->
<div  id="cue-wordpress-forms-container">
  <?php

  if (!empty($errors) && is_array($errors)) {
  ?>
  <div class="cue-form-notifications">
  <?php   
    foreach ($errors as $error) {
      ?>
  <p class="cue-form-error"><?php echo $error ?></p>
      <?php
    }
  ?>
  </div>
  <?php
  }
  ?>
  <div class="cue-auth-and-info-container">

    <div class="cue-info-container">
      <h2 class="title cue-title">Welcome to My List</h2>

      <p>Everything you care about in one place. Built just for you.</p>
      <ul>
        <li>Wishlist items</li>
        <li>Shares and Posts you want to remember</li>
        <li>Price Alerts on items you care about</li>
        <li>Special offers and rewards</li>
        <li>Always on, always available</li>
      </ul>
      <p>Goodbye forgetting. Hello remembering</p>
    </div>

    <div class="cue-auth-container">
      <!-- register form START -->


      <div id="cue-register" class="cue-form-container cue-hidden">

        <h2 class="cue-title">Create Account</h2>
        <form action="" class="cue-form" method="post">
        <div class="cue-form-row req">
          <label for="cue-register-email">Email:</label>
          <input type="email" name="cue_register_email" id="cue-register-email" class="required" value="<?php echo $register["cue_register_email"]; ?>">
        </div>
        <div class="cue-form-row req">
          <label for="cue-register-password">Password:</label>
          <input type="password" name="cue_register_password" id="cue-register-password" value="<?php echo $register["cue_register_password"]; ?>">
        </div>
        <div class="cue-form-row">
          <label for="cue-register-fname">First Name:</label>
          <input type="text" name="cue_register_fname" id="cue-register-fname" value="<?php echo $register["cue_register_fname"]; ?>">
        </div>
        <div class="cue-form-row">
          <label for="cue-register-lname">Last Name:</label>
          <input type="text" name="cue_register_lname" id="cue-register-lname" value="<?php echo $register["cue_register_lname"]; ?>">
        </div>
        <div class="cue-form-row">
          <input type="submit" id="cue-register-submit" value="Register">
        </div>
        <input type="hidden" name="cue_action" value="register" id="cue-login-action">

        <!-- Spam Trap -->
        <div style="<?php echo ((is_rtl())?'right':'left'); ?>: -999em; position: absolute;"><label for="trap"><?php _e('Anti-spam', 'woocommerce'); ?></label><input type="text" name="email_2" id="trap" tabindex="-1" /></div>

        <?php wp_nonce_field('cue-register-nonce'); ?>

        <?php do_action('woocommerce_register_form_end'); ?>
        </form>
        <p>
          <a href="#signin" class="cue-js-show-login">&larr; Back to Login form</a>    
        </p>

      </div>

      <div id="cue-login" class="cue-form-container">
        <h2 class="cue-title">Login</h2>
        <form action="" class="cue-form" method="post">
        <div class="cue-form-row">
          <label for="cue-login-email">Email:</label>
          <input type="email" name="cue_login_email" id="cue-login-email" value="<?php echo $login["cue_login_email"]; ?>">
        </div>
        <div class="cue-form-row">
          <label for="cue-login-password">Password:</label>
          <input type="password" name="cue_login_password" id="cue-login-password" value="<?php echo $login["cue_login_password"]; ?>">
        </div>
        <div class="cue-form-row">
          <label>
            Remember me <input type="checkbox" name="cue_login_remember" value="1" <?php echo empty($login['cue_login_remember'])?'':'checked'; ?> id="">
          </label>
        </div>
        <div class="cue-form-row">
          <input type="submit" id="cue-login-submit" value="Login">
        </div>
        <input type="hidden" name="cue_action" value="login" id="cue-login-action">
        <?php wp_nonce_field('cue-login-nonce'); ?>
        </form>
        <p>
          <a href="<?php echo wp_lostpassword_url('/apps/mylist'); ?>">Forgot Password?</a>                
        </p>
        <p>
          Don't have an account? <a class="cue-js-show-signup" href="#signup" >Sign up</a>
        </p>
      </div>
    </div>
  </div>

  
</div>

<!-- END CUE LOGIN FORM -->

<div id="cue-faq-container" style="display: none;">
  <iframe
    id="faq_container_iframe"
    class="faqcontainer"
    name="streamIFrame"
    src="https://www.cueconnect.com/poweredby/<?php echo self::$_options['place_id']; ?>/?origin=<?php bloginfo('url'); ?>&amp;action=faq"
    scrolling="no">
  </iframe>
</div>

<div id="cue-external-footer">
  <div id="cue_links" class="cue-links">
    <a href="#" id="open_learnmore">Learn More</a> | <a href="#" id="open_faq" class="faqs" >FAQs</a>
  </div>
  <div class="cue-links">
    <a id="go_back" style="display: none;" href="#">« Go Back</a>
  </div>
  <div class="cue-links-right">
    <span id="imi-external-footer-title">My List</span> powered by
    <a href="http://www.cueconnect.com" target="_BLANK">
      <svg height="10" id="Layer_1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 431.6 147.8" style="max-width:35px"><style>.cueWhiteNoTMSVG0{fill:#000}.cueWhiteNoTMSVG1{display:none}.cueWhiteNoTMSVG2{display:inline;fill:#000}</style><path class="cueWhiteNoTMSVG0" d="M124.2 111.6c-2.1-2.1-4.2-2.1-6.7.7-3.9 4.2-17.6 20.8-44 20.8-31.3 0-57.7-25.3-57.7-59.5 0-33.4 26-58.4 58.1-58.4 22.2 0 36.9 12.3 40.5 16.2 2.1 2.1 4.9 3.2 7.4.7l3.5-3.5c2.1-1.8 2.5-4.2.7-6.3C119.6 14.1 101.3 0 73.9 0 34.1 0 0 31.7 0 73.9c0 42.6 34.1 73.9 73.9 73.9 29.2 0 50-18.7 55.2-27.1 1.4-2.1.7-3.9-1-5.6l-3.9-3.5zM412.3 24.6C385.4-4.8 339-8.5 307.9 20c-31.4 28.8-31.4 75.1-4.5 104.4 19.7 21.5 47.5 24.2 57.3 22.4 2.5-.4 3.3-2.1 3.4-4.6V137c.1-3-1.3-4.5-5-4.5-5.7 0-27.2 1.1-45-18.4-21.2-23.1-20.3-59.7 4.8-82.7 24.6-22.6 60.7-20.3 82.3 3.3 15 16.3 15.9 35.6 15.4 40.8-.1 3 .2 4.9 3.7 5l7.3.6c2.7.4 3.2-.5 3.6-3.3 1.6-10-.4-33-18.9-53.2z"/><path class="cueWhiteNoTMSVG0" d="M386.3 66.5h-56.8c-1.6 0-2.9 1.3-2.9 2.9v8.9c0 1.6 1.3 2.9 2.9 2.9h56.8c1.6 0 2.9-1.3 2.9-2.9v-8.9c0-1.7-1.3-2.9-2.9-2.9zM267.2 3.7c0-1.6-1.3-2.9-2.9-2.9h-9.1c-1.6 0-2.9 1.3-2.9 2.9v80.9c0 .2.1.3.1.5v1.8c0 12.6-4.5 23.4-13.4 32.2-8.9 8.8-19.7 13.3-32.3 13.5-12.4-.2-23.1-4.7-31.9-13.5-8.8-8.8-13.2-19.6-13.2-32.2V3.7c0-1.6-1.3-2.9-2.9-2.9H150c-1.6 0-2.9 1.3-2.9 2.9v83.1c0 16.6 5.8 30.8 17.4 42.6 11.6 11.7 25.8 17.6 42.4 17.6 16.6 0 30.8-5.9 42.6-17.6 11.7-11.7 17.6-25.9 17.6-42.6V3.7z"/><g class="cueWhiteNoTMSVG1"><path class="cueWhiteNoTMSVG2" d="M413.3 1.5v-1h6.9v1h-2.9v7.6h-1.1V1.5h-2.9zM422.7.5l2.7 7.2 2.7-7.2h1.6v8.6h-1.1V1.9L425.9 9h-1l-2.7-7.1V9h-1.1V.5h1.6z"/></g></svg>
    </a>
  </div>
</div>

<!-- CUE LOGIN / REGISTER SCRIPT -->

<script>
jQuery(function($) {
    
  $(window).on('load', function() {
    cueCheckLocation();
    cueBindHooks();
  });

  function cueBindHooks() {
    $('.cue-js-show-signup').on('click', function(e) {
      cueShowRegisterForm();
      e.stopPropagation();
    });
    $('.cue-js-show-login').on('click', function(e) {
      cueShowLoginForm();
      e.stopPropagation();
    });
  }

  function cueShowLoginForm() {
    $('#cue-register').addClass('cue-hidden');
    $('#cue-login').removeClass('cue-hidden'); 
  }

  function cueShowRegisterForm() {
    $('#cue-register').removeClass('cue-hidden');
    $('#cue-login').addClass('cue-hidden');
  }

  function cueCheckLocation() {
    switch (window.location.hash) {
      case "#signup" :
        cueShowRegisterForm();
      break;
      case "#signin" :
        cueShowLoginForm();
      break;
    }
  }

  var main_container = $('#cue-wordpress-forms-container');
  var faq_container = $('#cue-faq-container');
  var open_faq = $('#open_faq');
  var open_learnmore = $('#open_learnmore');
  var faq_container_iframe = $('#faq_container_iframe');
  var cue_links = $('#cue_links');
  var go_back = $('#go_back');

  open_faq.bind('click', function(e){
    main_container.hide();
    faq_container.show();
    go_back.show();
    cue_links.hide();
    e.preventDefault();
  });

  open_learnmore.bind('click', function(e){
    faq_container_iframe[0].contentWindow.postMessage('openTuto', faq_container_iframe[0].src);
    main_container.hide();
    faq_container.show();
    go_back.show();
    cue_links.hide();
    e.preventDefault();
  });

  go_back.bind('click', function(e){
    faq_container_iframe[0].contentWindow.postMessage('closeTuto', faq_container_iframe[0].src);
    main_container.show();
    faq_container.hide();
    go_back.hide();
    cue_links.show();
    e.preventDefault();
  });


  var retailerName = window.location.host;
  var retailerId = INMARKIT.retailId + '';
  document.getElementById('cue-login-email').addEventListener('click',function(){
    var eventCategory = "cp sign in email input";
    var eventAction = "cp sign in email input clicked";
    var eventLabel =  retailerId + " " + retailerName;
    ga('send', 'event', eventCategory, eventAction, eventLabel,  {
      hitCallback: function() {}
    });
  });
  document.getElementById('cue-login-password').addEventListener('click',function(){
    var eventCategory = "cp sign in password input";
    var eventAction = "cp sign in password input clicked";
    var eventLabel =  retailerId + " " + retailerName;
    ga('send', 'event', eventCategory, eventAction, eventLabel,  {
      hitCallback: function() {}
    });
  });
  document.getElementById('cue-login-submit').addEventListener('click',function(){
    var eventCategory = "cp sign in button";
    var eventAction = "cp sign in button clicked";
    var eventLabel =  retailerId + " " + retailerName;
    ga('send', 'event', eventCategory, eventAction, eventLabel,  {
      hitCallback: function() {}
    });
  });
  document.getElementById('open_learnmore').addEventListener('click',function(){
    var eventCategory = "cp learn more";
    var eventAction = "cp learn more clicked";
    var eventLabel =  retailerId + " " + retailerName;
    ga('send', 'event', eventCategory, eventAction, eventLabel,  {
      hitCallback: function() {}
    });
  });
  document.getElementById('open_faq').addEventListener('click',function(){
    var eventCategory = "cp faq";
    var eventAction = "cp faq clicked";
    var eventLabel =  retailerId + " " + retailerName;
    ga('send', 'event', eventCategory, eventAction, eventLabel,  {
      hitCallback: function() {}
    });
  });
  document.getElementById('cue-register-email').addEventListener('click',function(){
    var eventCategory = "cp sign up email input";
    var eventAction = "cp sign up email input clicked";
    var eventLabel =  retailerId + " " + retailerName;
    ga('send', 'event', eventCategory, eventAction, eventLabel,  {
      hitCallback: function() {}
    });
  });
  document.getElementById('cue-register-password').addEventListener('click',function(){
    var eventCategory = "cp sign up password input";
    var eventAction = "cp sign up password input clicked";
    var eventLabel =  retailerId + " " + retailerName;
    ga('send', 'event', eventCategory, eventAction, eventLabel,  {
      hitCallback: function() {}
    });
  });
  document.getElementById('cue-register-fname').addEventListener('click',function(){
    var eventCategory = "cp sign up first name input";
    var eventAction = "cp sign up first name input clicked";
    var eventLabel =  retailerId + " " + retailerName;
    ga('send', 'event', eventCategory, eventAction, eventLabel,  {
      hitCallback: function() {}
    });
  });
  document.getElementById('cue-register-lname').addEventListener('click',function(){
    var eventCategory = "cp sign up last name input";
    var eventAction = "cp sign up last name input clicked";
    var eventLabel =  retailerId + " " + retailerName;
    ga('send', 'event', eventCategory, eventAction, eventLabel,  {
      hitCallback: function() {}
    });
  });
  document.getElementById('cue-register-submit').addEventListener('click',function(){
    var eventCategory = "cp sign up button";
    var eventAction = "cp sign up button clicked";
    var eventLabel =  retailerId + " " + retailerName;
    ga('send', 'event', eventCategory, eventAction, eventLabel,  {
      hitCallback: function() {}
    });
  });
});
</script>