
        <?php

require __DIR__.'/vendor/autoload.php';

        ?>
        <?php include("./header.php") ?>

<div id="app">
<div class="section">
<div class="container">

  <p class="title">Send to topic</p>
  <form action="./api/?api=messaging&action=send_to_topic" method="post">
  <div class="field">
    <label class="label">Title</label>
    <div class="control"><input type="text" name="title" /></div>
  </div>
  <div class="field">
    <label class="label">body</label>
    <div class="control"><textarea name="body"
    rows="10" cols="50"></textarea>
    </div>
  </div>
  <div class="field">
    <label class="label">Topic</label>
    <div class="control">
    <select name="topic">
    <option value="cdpnews">cdpnews</option>
    <option value="ita_plus">ita_plus</option>
    <option value="ita_pro">ita_pro</option>
    </select>
    </div>
  </div>

  <div class="field is-grouped is-grouped-multiline">
    <label class="label">Pagina</label>
    <div class="control">
    <select name="data_view">
    <option value="category">category</option>
    <option value="product">product</option>
    <option value="post">post</option>
    </select>
    </div>
    <label class="label">ID</label>
    <div class="control">
    <input type="text" name="data_id" />
    </div>
  </div>

  <div class="field">
    <div class="control">
    <input class="button" type="submit" />
    </div>
  </div>
  </form>

</div><!-- container -->
</div><!-- section -->
<div class="section">
<div class="container">

<p class="title">Send to token</p>
<form action="./api/?api=messaging&action=send_to_token" method="post">
 <p>Title: <input type="text" name="title" /></p>
 <p>body: <textarea name="body"
   rows="10" cols="50"></textarea></p>
 <p>Token: <input type="text" name="token" /></p>
 <p><input type="submit" /></p>
</form>

</div><!-- container -->
</div><!-- section -->
<div class="section">
<div class="container">

<p class="title">Token Info</p>
<form action="./api/?api=messaging&action=token_info" method="post">
 <p>Token: <textarea name="token"
   rows="10" cols="50"></textarea></p>
   <p>Info type: <select name="infotype">
  <option value="info">info</option>
  <option value="subscriptions">subscriptions</option>
  </select></p>

 <p><input type="submit" /></p>
</form>

</div><!-- container -->
</div><!-- section -->
</div><!-- app -->

<script src="app/index.js?3"></script>

<?php include("./footer.php") ?>
