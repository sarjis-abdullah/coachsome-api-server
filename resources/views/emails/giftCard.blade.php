<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700;800&family=Pacifico&display=swap" rel="stylesheet">

  <style type="text/css">
    @page {
      margin: 0px;
    }

    body {
      margin: 0px;
    }

    .gift-card .top-section .top-section__title {
      font-family: 'Pacifico', cursive;
      font-weight: 400;
      font-style: normal;
      font-size: 48px;
      display: flex;
      align-items: center;
      text-align: center;
      color: #6EB5CB;
    }

    .gift-card .top-section .top-section__description {
      width: 100%;
      font-family: Pacifico;
      font-style: normal;
      font-weight: normal;
      font-size: 18px;
      text-align: center;
      color: #49556a;
    }

    .middle-section .code-title {
      font-family: Roboto;
      font-style: normal;
      font-weight: normal;
      font-size: 12px;
      line-height: 14px;
      text-align: center;
      color: #9faec2;
    }

    .middle-section .code {
      font-family: Open Sans;
      font-weight: 800;
      font-size: 24px;
      line-height: 33px;
      text-align: center;
      color: #6EB5CB;
    }

    .middle-section .help-text {
      font-family: Open Sans;
      font-weight: normal;
      font-size: 14px;
      line-height: 27px;
      text-align: center;
      color: #000000;
    }

    .middle-section .code-value {
      font-family: Open Sans;
      font-weight: 800;
      font-size: 48px;
      line-height: 65px;
      text-align: center;
      color: #d61ba2;
    }

    .middle-section .how-to-use {
      font-family: Open Sans;
      font-weight: 800;
      font-size: 18px;
      line-height: 25px;
      text-align: center;
      color: #6EB5CB;
    }

    .steps {
      width: 100%;
      margin-top: 20px;
      margin-bottom: 20px;
    }

    .steps .step-card {
      float: left;
      width: 25%;
      background: #ecf2f7;
      border-radius: 12px;
      flex-grow: 1;
      padding: 10px;
      margin: 20px;
      display: flex;
    }

    .steps .step-card .step-card__title {
      font-family: Open Sans;
      font-weight: 800;
      font-size: 18px;
      line-height: 25px;
      text-align: center;
      color: #15577C;
    }

    .steps .step-card .step-card__description {
      font-family: Open Sans;
      font-weight: 600;
      font-size: 12px;
      line-height: 16px;
      text-align: center;
      color: #2c3749;
    }

    .bottom-section {
      clear: left;
      background: #15577C;
    }

    .bottom-section__title {
      width: 100%;
      padding: 20px;
      text-align: center;

    }

    .bottom-section__title img {
      height: 26px;
    }

    .bottom-section__description {
      font-family: Open Sans;
      font-weight: bold;
      padding-top: 12px;
      padding-bottom: 20px;
      font-size: 48px;
      text-align: center;
      color: #e1e8f1;
    }
    }
  </style>
</head>

<body>
  <div class="downloadable-card">
    <div class="gift-card">
      <div class="top-section">
        <div class="top-section__title">
          Dear :firstName :lastName
        </div>
        <div class="top-section__description">
          :GiftcardText Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem
          ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum
          Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem
          ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum
        </div>
      </div>
      <div class="middle-section">
        <div class="code-title">
          GIFT CARD CODE
        </div>
        <div class="code">
          :code
        </div>
        <div class="help-text">
          From :firstName :lastName - I hope this can help you on your
          way
        </div>
        <div class="code-value">:value :currency</div>
        <div class="how-to-use">
          How to use your gift
        </div>
        <div class="steps">
          <div class="step-card">
            <div class="step-card__title">
              1
            </div>
            <div class="step-card__description">
              Select your sport at Coachsome.com
            </div>
          </div>
          <div class="step-card">
            <div class="step-card__title">
              2
            </div>
            <div class="step-card__description">
              Find & book a coach
            </div>
          </div>
          <div class="step-card">
            <div class="step-card__title">
              3
            </div>
            <div class="step-card__description">
              On checkout, use code :code
            </div>
          </div>
        </div>
      </div>
      <div class="bottom-section">
        <div class="bottom-section__title">
          <img src="https://api.coachsome.com/assets/images/logos/logo-light.png" alt="logo" />
        </div>
        <div class="bottom-section__description">
          GIFT CERTIFICATE
        </div>
      </div>
    </div>
  </div>
</body>

</html>