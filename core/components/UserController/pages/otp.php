<head>


     <!-- Bootstrap CSS -->
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <style>
        .otp-field {
            flex-direction: row;
            column-gap: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            }

            .otp-field input {
            height: 45px;
            width: 42px;
            border-radius: 6px;
            outline: none;
            font-size: 1.125rem;
            text-align: center;
            border: 1px solid #ddd;
            }
            .otp-field input:focus {
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.1);
            }
            .otp-field input::-webkit-inner-spin-button,
            .otp-field input::-webkit-outer-spin-button {
            display: none;
            }

            .resend {
            font-size: 12px;
            }

            .footer {
            position: absolute;
            bottom: 10px;
            right: 10px;
            color: black;
            font-size: 12px;
            text-align: right;
            font-family: monospace;
            }

            .footer a {
            color: black;
            text-decoration: none;
            }
    </style>
</head>
<body class="container-fluid bg-body-tertiary d-block">
  <div class="row justify-content-center">
      <div class="col-12 col-md-6 col-lg-4" style="min-width: 500px;">
        <div class="card bg-white mb-5 mt-5 border-0" style="box-shadow: 0 12px 15px rgba(0, 0, 0, 0.02);">
          <div class="card-body p-5 text-center">
            <h4>Verify</h4>
            <p>Your code was sent to you via SMS</p>
                <form action="<?=$_ENV['URL_HOST'].'otp' ?>" method="POST">
                    <input type="hidden" name="email" value="<?=$email?>" >
                    <input type="hidden" name="type" value="<?=isset($_GET["type"]) ? $_GET["type"]  : ''?>" >

                    <div class="otp-field mb-4">
                        <input type="number" name="pin[]" />
                        <input type="number" name="pin[]" disabled />
                        <input type="number" name="pin[]" disabled />
                        <input type="number" name="pin[]" disabled />
                        <input type="number" name="pin[]" disabled />
                        <input type="number" name="pin[]" disabled />
                    </div>

                    <button class="btn btn-primary mb-3" type="submit">
                    Verify
                    </button>

                </form>
           

           
            </p>
          </div>
        </div>
      </div>
    </div>


  <script>
    const inputs = document.querySelectorAll(".otp-field > input");
const button = document.querySelector(".btn");

window.addEventListener("load", () => inputs[0].focus());
button.setAttribute("disabled", "disabled");

inputs[0].addEventListener("paste", function (event) {
  event.preventDefault();

  const pastedValue = (event.clipboardData || window.clipboardData).getData(
    "text"
  );
  const otpLength = inputs.length;

  for (let i = 0; i < otpLength; i++) {
    if (i < pastedValue.length) {
      inputs[i].value = pastedValue[i];
      inputs[i].removeAttribute("disabled");
      inputs[i].focus;
    } else {
      inputs[i].value = ""; // Clear any remaining inputs
      inputs[i].focus;
    }
  }
});

inputs.forEach((input, index1) => {
  input.addEventListener("keyup", (e) => {
    const currentInput = input;
    const nextInput = input.nextElementSibling;
    const prevInput = input.previousElementSibling;

    if (currentInput.value.length > 1) {
      currentInput.value = "";
      return;
    }

    if (
      nextInput &&
      nextInput.hasAttribute("disabled") &&
      currentInput.value !== ""
    ) {
      nextInput.removeAttribute("disabled");
      nextInput.focus();
    }

    if (e.key === "Backspace") {
      inputs.forEach((input, index2) => {
        if (index1 <= index2 && prevInput) {
          input.setAttribute("disabled", true);
          input.value = "";
          prevInput.focus();
        }
      });
    }

    button.classList.remove("active");
    button.setAttribute("disabled", "disabled");

    const inputsNo = inputs.length;
    if (!inputs[inputsNo - 1].disabled && inputs[inputsNo - 1].value !== "") {
      button.classList.add("active");
      button.removeAttribute("disabled");

      return;
    }
  });
});

  </script>
</body>