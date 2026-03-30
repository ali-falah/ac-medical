var HighData = [];
var BachData = [];
var selectedType = "";
var total = 0;
var MainType = 0; // 1 bach, 2 high
var selectedStudentId = 0;
var fileSelected = 0;
var lastPaymentNum = 0;
var DisCanBeAddedOrUpdated = false;
var UsedremainInDeletion = 0;
var totalAfterDis = 0;
var AcceptTypeTotal = 0;
var deleteButtonClickCounter = 1;

function formatIQD(amount) {
  return Number(amount).toLocaleString("en-US").split('.')[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",") + " د.ع";
}

function handleAjaxError(jqXHR, textStatus, errorMessage) {
  console.error("AJAX Error:", errorMessage);
  showError("حدث خطأ في الاتصال: " + errorMessage);
}

const closeStudentView = () => {
  $("#view-Student").removeClass("active");
  $("body").removeClass("drawer-open");
  // Clear and refetch the student list
  $("#students_table").html("");
  GetAllBachStudents();
  GetAllHighStudents();
};

function resetPaymentForm() {
  closeStudentView();
  $("#AllRadioButton").prop("checked", true);
  $("#BachRadioButton, #HighRadioButton").prop("checked", false);
  $("#students_table").html("");
  $("#PayTheRemainRemarks, #PaytheRemainInput, #PayTheRemainFileInput, #SearchStudentInput").val("");
  $("#PayTheRemainImgPreview").hide().attr("src", "");
  fileSelected = 0;
  GetAllBachStudents();
  GetAllHighStudents();
}

function calculateDiscount() {
  let disPer = Number($("#DiscountPerInput").val()) || 0;
  const $saveBtn = $("#SaveDiscountToDB");
  
  // Validation: Discount must be between 0 and 100
  if (disPer < 0 || disPer > 100) {
    $("#DiscountPerInput").addClass("is-invalid");
    $saveBtn.prop("disabled", true);
    $("#grandTotal").val("خطأ في النسبة").addClass("text-danger").removeClass("text-success");
    return;
  } else {
    $("#DiscountPerInput").removeClass("is-invalid");
    $("#grandTotal").removeClass("text-danger").addClass("text-success");
  }

  if (AcceptTypeTotal > 0) {
    const discountAmount = (AcceptTypeTotal * disPer) / 100;
    totalAfterDis = AcceptTypeTotal - discountAmount;
    $("#grandTotal").val(formatIQD(totalAfterDis));
    $("#dispNetAmount").text(formatIQD(totalAfterDis));
    $("#dispDiscountPer").text(disPer + "%");
    
    // Disable if balance is 0 or less, or if invalid range
    $saveBtn.prop("disabled", totalAfterDis < 0 || !DisCanBeAddedOrUpdated);
  } else {
    totalAfterDis = 0;
    $("#grandTotal").val(formatIQD(0));
    $("#dispNetAmount").text(formatIQD(0));
    $("#dispDiscountPer").text("0%");
    $saveBtn.prop("disabled", true);
  }
}

$(document).ready(function () {


  $("#DiscountPerInput").on("input change", function (e) {
    // Allow only numeric digits, prevent negative and text
    let val = this.value;
    if (val !== "" && val < 0) this.value = 0;
    if (val !== "" && val > 100) this.value = 100;
    
    calculateDiscount();
  });

  GetAllBachStudents();
  GetAllHighStudents();
  $("#students_table").html("");

  $("#printIcon").click(function () {
    window.print();
  });

  ///////////file upload and preview and print ///////////////////////////////////////
  const inputElement = document.getElementById("PayTheRemainFileInput");
  const previewElement = document.getElementById("PayTheRemainImgPreview");

  inputElement.addEventListener("change", (event) => {
    const file = event.target.files[0];
    const reader = new FileReader();

    if (
      (file && file.name.endsWith(".png")) ||
      (file && file.name.endsWith(".jpeg")) ||
      (file && file.name.endsWith(".jpg"))
    ) {
      reader.readAsDataURL(file);
      $("#PayTheRemainImgPreview").css("display", "block");
      reader.onload = (e) => {
        previewElement.src = e.target.result;
      };

      fileSelected = file;
    } else {
      showWarning(`الرجاء اختيار صورة فقط الامتدادات المدعومة 
                   .jpg , .png , .jpeg`);
      inputElement.value = "";
    }
  });

  $("#PayTheRemainImgPreview").click(function (e) {
    e.preventDefault();
    // window.open($(this).attr("src"), "_blank");

    const imageSrc = $(this).attr("src");
    const newTab = window.open("", "_blank");
    newTab.document.write(
      `<html><head><title>وصل الدفع</title></head><body><img  src="${imageSrc}"></body></html>`,
    );

    setTimeout(() => {
      newTab.print();
    }, 2000);
  });

  ///file upload and preview///////////////////////////////////////////

  $("#SaveDiscountToDB").click(function (e) {
    calculateDiscount(); // Ensure latest values are used
    
    if (selectedStudentId <= 0) {
      showWarning("يرجى اختيار طالب أولاً");
      return;
    }

    if (!DisCanBeAddedOrUpdated) {
      showWarning("لا يمكن تعديل خصم هذا العميل");
      return;
    }

    const disPer = Number($("#DiscountPerInput").val());
    const postData = {
      totalAfterDis: totalAfterDis,
      disPer: disPer
    };

    if (MainType == 1) { // Bach
      postData.StudentBachIdToAddOrUpdateDiscount = selectedStudentId;
    } else { // High
      postData.StudentHighIdToAddOrUpdateDiscount = selectedStudentId;
    }

    $.ajax({
      type: "post",
      url: "php_action/accountQueries.php",
      data: postData,
      dataType: "text",
      success: function (response) {
        if (response == "ok") {
          showSuccess("تم حفظ الخصم");
          // Refresh only the stats by re-calling rowClicked logic or just update remain
          rowClicked(selectedStudentId, MainType); 
        } else {
          showError(response);
        }
      },
      error: handleAjaxError,
    });
  });

  function parseCurrency(value) {
    if (typeof value !== 'string') return Number(value) || 0;
    return Number(value.replace(/[^0-9.]/g, ""));
  }

  $("#PaytheRemainInput").on("input keyup", function (e) {
    // Strip non-digits during input for strict behavior
    let sanitized = this.value.replace(/[^0-9]/g, "");
    if (this.value !== sanitized) {
      this.value = sanitized;
    }

    let val = Number(sanitized) || 0;

    // Real-time calculation
    const currentRemain = Number(total); // This is the total_remain from DB
    const newRemain = currentRemain - val;
    
    $("#RemainInput").val(formatIQD(newRemain));
    
    if (newRemain < 0) {
      $("#RemainInput").addClass("text-danger").removeClass("text-warning");
      $("#PaytheRemainSaveButton").prop("disabled", true);
    } else {
      $("#RemainInput").removeClass("text-danger").addClass("text-warning");
      $("#PaytheRemainSaveButton").prop("disabled", val <= 0);
    }
  });

  // Final formatting on blur
  $("#PaytheRemainInput").on("blur", function() {
    let val = Number(this.value.replace(/[^0-9]/g, "")) || 0;
    if (val > 0) {
      $(this).val(formatIQD(val));
    }
  });

  // On focus, show raw number for easier editing
  $("#PaytheRemainInput").on("focus", function() {
    let val = Number(this.value.replace(/[^0-9]/g, "")) || 0;
    if (val > 0) {
      $(this).val(val);
    } else {
      $(this).val("");
    }
  });

  function filterStudents() {
    const searchValue = $("#SearchStudentInput").val().toLowerCase();
    const isAll = $("#AllRadioButton").prop("checked");
    const isBach = $("#BachRadioButton").prop("checked");
    const isHigh = $("#HighRadioButton").prop("checked");

    $("#students_table").html("");

    if (searchValue.length === 0 && !isAll && !isBach && !isHigh) return;

    if (isAll || isBach) {
      const filteredBach = BachData.filter(obj => 
        String(obj.name || "").toLowerCase().includes(searchValue) ||
        String(obj.std_id || "").includes(searchValue) ||
        String(obj.remarks || "").toLowerCase().includes(searchValue)
      );
      appendToTable(filteredBach, 1);
    }

    if (isAll || isHigh) {
      const filteredHigh = HighData.filter(obj => 
        String(obj.name || "").toLowerCase().includes(searchValue) ||
        String(obj.std_id || "").includes(searchValue) ||
        String(obj.remarks || "").toLowerCase().includes(searchValue)
      );
      appendToTable(filteredHigh, 2);
    }
  }

  $("#AllRadioButton, #HighRadioButton, #BachRadioButton").click(function () {
    const id = $(this).attr("id");
    if (id === "AllRadioButton") {
      $("#BachRadioButton, #HighRadioButton").prop("checked", false);
    } else if (id === "HighRadioButton") {
      $("#BachRadioButton, #AllRadioButton").prop("checked", false);
    } else if (id === "BachRadioButton") {
      $("#HighRadioButton, #AllRadioButton").prop("checked", false);
    }
    filterStudents();
  });

  $("#SearchStudentInput").keyup(filterStudents);

  $("#closeTimesButton").click(function (e) {
    e.preventDefault();
    $("#view-Student").removeClass("active");
    $("body").removeClass("drawer-open");
  });

  $("#PaytheRemainSaveButton").click(function (e) {
    e.preventDefault();
    paymentRemarks = $("#PayTheRemainRemarks").val();

    // Parse payment amount properly - handle both formatted and raw input
    const paymentInputVal = $("#PaytheRemainInput").val();
    const paymentAmount = Number(regexToGetOnlyNumFromCurrencyAndCommas(paymentInputVal));
    
    if (
      !paymentInputVal || paymentAmount <= 0 ||
      $("#RemainInput").val() == "" ||
      paymentRemarks == "" ||
      fileSelected === 0
    ) {
      showWarning("يرجى ملئ البيانات");
    } else {
      // Use the already parsed paymentAmount
      amountPaid = paymentAmount;
      remianAmount = Number(
        regexToGetOnlyNumFromCurrencyAndCommas($("#RemainInput").val()),
      );

      const formData1 = new FormData();
      formData1.append("paymentImg", fileSelected);
      formData1.append("paymentNum", Number(lastPaymentNum) + 1);
      formData1.append("paidAmount", amountPaid);
      formData1.append("remain", remianAmount);
      formData1.append("remarks", paymentRemarks);

      if (MainType === 1) {
        // bach student
        formData1.append("BachStudentId", selectedStudentId);

        $.ajax({
          url: "php_action/accountQueries.php",
          type: "POST",
          processData: false,
          contentType: false,
          data: formData1,
          success: function (response) {
            if (response.trim() === "ok") {
              showSuccess("تم حفظ الدفعة بنجاح");
              // Clear only the payment inputs
              $("#PaytheRemainInput, #PayTheRemainRemarks, #PayTheRemainFileInput").val("");
              $("#PayTheRemainImgPreview").hide().attr("src", "");
              fileSelected = 0;
              // Close the drawer and refresh the main table
              closeStudentView();
            } else {
              showError(response || "خطأ في حفظ الدفعة");
            }
          },
          error: handleAjaxError,
        });
      } else {
        formData1.append("HighStudentId", selectedStudentId);

        $.ajax({
          url: "php_action/accountQueries.php",
          type: "POST",
          processData: false,
          contentType: false,
          data: formData1,
          success: function (response) {
            if (response.trim() === "ok") {
              showSuccess("تم حفظ الدفعة بنجاح");
              // Clear only the payment inputs
              $("#PaytheRemainInput, #PayTheRemainRemarks, #PayTheRemainFileInput").val("");
              $("#PayTheRemainImgPreview").hide().attr("src", "");
              fileSelected = 0;
              // Close the drawer and refresh the main table
              closeStudentView();
            } else {
              showError(response || "خطأ في حفظ الدفعة");
            }
          },
          error: handleAjaxError,
        });
      }
    }
  });
});

function GetAllBachStudents() {
  $.ajax({
    type: "post",
    url: "php_action/accountQueries.php",

    data: {
      getBachAllStudents: "getBachAllStudents",
    },
    dataType: "json",
    success: function (response) {
      BachData = response;
      selectedType = "بكلوريوس";

      if (
        $("#AllRadioButton").prop("checked") ||
        $("#BachRadioButton").prop("checked")
      ) {
        appendToTable(response, 1);
      }
    },
  });
}

function GetAllHighStudents() {
  $.ajax({
    type: "post",
    url: "php_action/accountQueries.php",

    data: {
      GetAllHighStudents: "GetAllHighStudents",
    },
    dataType: "json",
    success: function (response) {
      HighData = response;

      if (
        $("#AllRadioButton").prop("checked") ||
        $("#HighRadioButton").prop("checked")
      ) {
        appendToTable(response, 2);
      }
    },
  });
}

function rowClicked(id, type) {
  $("#view-Student").addClass("active");
  $("body").addClass("drawer-open");
  $("#PayTheRemainImgPreview").css("display", "none");
  $("#PayTheRemainFileInput").val("");
  $("#PayTheRemainRemarks").val("");
  $("#PaytheRemainInput").val("");

  $("#RemainInput").val("");
  $("#DiscountPerInput").val("");
  $("#grandTotal").val("");
  fileSelected = 0;
  selectedStudentId = id;
  MainType = type;
  $("#DiscountPerInput").prop("disabled", false);
  $("#SaveDiscountToDB").prop("disabled", false);
  if (type === 1) {
    $.ajax({
      type: "post",
      url: "php_action/accountQueries.php",
      data: {
        StudentBachIdToGetHisInfo: id,
      },
      dataType: "json",
      success: function (response) {
        appendToStudentInfoTable(response);
        total = response.total_remain;
        AcceptTypeTotal = Number(response.amount);

        if (Number(response.total_remain) == 0) {
          // discount validation
          DisCanBeAddedOrUpdated = false;
          $("#DiscountPerInput").prop("disabled", true);
          $("#SaveDiscountToDB").prop("disabled", true);
        } else {
          DisCanBeAddedOrUpdated = true;
        }

        $("#DiscountPerInput").val(response.discount_percentage);
        $("#dispDiscountPer").text(response.discount_percentage + "%");
        $("#dispTotalAmount").text(formatIQD(response.amount));
        $("#dispTotalRemain").text(formatIQD(response.total_remain));
        $("#RemainInput").val(formatIQD(response.total_remain));
        $("#dispTotalPaid").text(formatIQD(Number(response.amount) - Number(response.total_remain)));

        calculateDiscount();
        if (total == 0) {
          $("#PaytheRemainInput").prop("disabled", true);
          $("#PayTheRemainRemarks").prop("disabled", true);
          $("#PayTheRemainFileInput").prop("disabled", true);
          $("#PaytheRemainSaveButton").prop("disabled", true);
        } else {
          $("#PaytheRemainInput").prop("disabled", false);
          $("#PayTheRemainRemarks").prop("disabled", false);
          $("#PayTheRemainFileInput").prop("disabled", false);
          $("#PaytheRemainSaveButton").prop("disabled", false);
        }
        //getStudentPayments

        $.ajax({
          type: "post",
          url: "php_action/accountQueries.php",
          data: {
            StudentBachIdToGetHisPayments: id,
          },
          dataType: "json",
          success: function (response) {
            appendToPaymentInfoTable(response, 1); // 1 for bach
          },
        });
      },
    });
  } else {
    $.ajax({
      type: "post",
      url: "php_action/accountQueries.php",
      data: {
        StudentHighIdToGetHisInfo: id,
      },
      dataType: "json",
      success: function (response) {
        appendToStudentInfoTable(response);

        if (response.total_amount == response.total_remain) {
          DisCanBeAddedOrUpdated = true;
        } else {
          DisCanBeAddedOrUpdated = false;
          $("#DiscountPerInput").prop("disabled", true);
          $("#SaveDiscountToDB").prop("disabled", true);
        }

        if (Number(response.IsPublic) == 1) {
          DisCanBeAddedOrUpdated = false;
          $("#DiscountPerInput").prop("disabled", true);
          $("#SaveDiscountToDB").prop("disabled", true);
        }

        total = response.total_remain;
        AcceptTypeTotal = Number(response.amount);
        console.log(response.discount_percentage);
        $("#DiscountPerInput").val(response.discount_percentage);
        $("#dispDiscountPer").text(response.discount_percentage + "%");
        $("#dispTotalAmount").text(formatIQD(response.amount));
        $("#dispTotalRemain").text(formatIQD(response.total_remain));
        $("#RemainInput").val(formatIQD(response.total_remain));
        $("#dispTotalPaid").text(formatIQD(Number(response.amount) - Number(response.total_remain)));

        calculateDiscount();
        if (total == 0) {
          $("#PaytheRemainInput").prop("disabled", true);
          $("#PayTheRemainRemarks").prop("disabled", true);
          $("#PayTheRemainFileInput").prop("disabled", true);
          $("#PaytheRemainSaveButton").prop("disabled", true);
        } else {
          $("#PaytheRemainInput").prop("disabled", false);
          $("#PayTheRemainRemarks").prop("disabled", false);
          $("#PayTheRemainFileInput").prop("disabled", false);
          $("#PaytheRemainSaveButton").prop("disabled", false);
        }
        //getStudentPayments

        $.ajax({
          type: "post",
          url: "php_action/accountQueries.php",
          data: {
            StudentHighIdToGetHisPayments: id,
          },
          dataType: "json",
          success: function (response) {
            appendToPaymentInfoTable(response, 2); //2 for high 1 for bach
          },
        });
      },
    });
  }

  // if remain 0 then disable all
}

function appendToTable(data, type) {
  data.forEach((element) => {
    const stageWord = convertStageNumTOWord(Number(element.stage));
    const isHigh = type === 2;
    const acceptType = element.accept_type_name;
    const totalRemain = Number(element.total_remain);
    const totalAmount = Number(element.total_amount);
    
    // Highlight remain if it's not 0
    const remainClass = totalRemain > 0 ? "text-warning font-weight-bold" : "";

    $("#students_table").append(`
      <tr onclick="rowClicked(${element.std_id}, ${type})">
        <td>${element.std_id}</td>
        <td>${element.name}</td>
        <td>${stageWord}</td>
        <td>${isHigh ? "عليا" : "بكلوريوس"}</td>
        <td>${acceptType}</td>
        <td>${formatIQD(totalAmount)}</td>
        <td class="${remainClass}">${formatIQD(totalRemain)}</td>
        <td>${element.create_date || ""}</td>
      </tr>
    `);
  });
}

function convertStageNumTOWord(stage) {
  if (stage == 1) {
    return "اولى";
  }

  if (stage == 2) {
    return "ثانية";
  }
  if (stage == 3) {
    return "ثالثة";
  }
  if (stage == 4) {
    return "رابعة";
  }
  if (stage == 5) {
    return "خامسة";
  }
  if (stage == 6) {
    return "سادسة";
  }
}

function appendToStudentInfoTable(data) {
  $("#HighStudentOnlyData").css("display", "none");
  $("#PayTheRemainRemarks, #PaytheRemainInput, #RemainInput").val("");
  $("#studentInfoTable").html("");
  $("#studentRegisDate").html('<i class="fa fa-calendar mr-2"></i>' + data.create_date);
  $("#studentAcceptType span").text(data.AcceptTypeName);
  
  $("#dispTotalAmount").text(formatIQD(data.amount)); // Base Price
  $("#dispDiscountPer").text(data.discount_percentage + "%");
  $("#dispNetAmount").text(formatIQD(data.total_amount)); // Net Price after discount
  $("#dispTotalRemain").text(formatIQD(data.total_remain));
  $("#dispTotalPaid").text(formatIQD(Number(data.total_amount) - Number(data.total_remain)));

  $("#PayTheRemainDiv").css("display", "flex");

  UsedremainInDeletion = Number(data.total_remain);
  
  $("#studentInfoTable").append(`
    <tr>
      <td>${data.name}</td>
      <td>${formatIQD(data.amount)}</td>
      <td>${data.discount_percentage} %</td>
      <td>${formatIQD(data.total_amount)}</td>
      <td>${formatIQD(data.total_remain)}</td>
      <td>${convertStageNumTOWord(data.stage)}</td>
      <td>${data.remarks}</td>
    </tr>
  `);

  if (Number(data.total_remain) == 0) {
    $("#PayTheRemainDiv").css("display", "none");
  }

  if (MainType == 2) {
    $("#HighStudentOnlyData").css("display", "table");
    $("#HighStudentOnlyDataTableBody").html("");
    const isPublicText = data.IsPublic == 1 ? "عام" : "خاص";
    $("#HighStudentOnlyDataTableBody").append(`
      <tr>
        <td>${data.UniversityComand}</td>
        <td>${data.DateOfLaunch}</td>
        <td>${data.StudyCert}</td>
        <td>${data.TheOnlyCert}</td>
        <td>${isPublicText}</td>
      </tr>
    `);
  }
}

function appendToPaymentInfoTable(data, type) {
  $("#paymentInfoTable").html("");

  if (data && Array.isArray(data)) {
    data.forEach((element) => {
      const uploadDir = (type === 1) ? 'uploads' : 'uploadsHigh';
      const imgUrl = `<a target="_blank" href="php_action/${uploadDir}/${element.img}">اضغط هنا</a>`;
      const deleteId = (type === 1) ? element.payment_id : element.payment_high_id;
      
      const deleteButton = `
        <button onclick="DeletePayment(${deleteId}, ${type}, ${element.payment_amount})" 
                style="opacity:0.85;" 
                class="font-weight-bolder btn btn-sm px-4 py-2 btn-danger">
          حذف
        </button>`;

      lastPaymentNum = element.payment_num;

      $("#paymentInfoTable").append(`
        <tr>
          <td>${element.payment_num}</td>
          <td>${element.payment_date}</td>
          <td>${formatIQD(element.payment_amount)}</td>
          <td>${formatIQD(element.remain)}</td>
          <td>${element.remarks || ""}</td>
          <td>${imgUrl}</td>
          <td>${deleteButton}</td>
        </tr>
      `);
    });
  } else {
    console.log("No payments or public student");
  }
}

function regexToGetOnlyNumFromCurrencyAndCommas(value) {
  return String(value).replace(/[^0-9.]/g, "");
}

function DeletePayment(id, type, paymentValue) {
  const remainAfterDeletion = Number(paymentValue) + Number(UsedremainInDeletion);

  if (deleteButtonClickCounter >= 5) {
    const ajaxData = {
      selectedStudentId: selectedStudentId
    };

    if (type === 1) {
      ajaxData.PaymentIdToDelete = id;
    } else {
      ajaxData.PaymentHighIdToDelete = id;
    }

    $.ajax({
      type: "post",
      url: "php_action/accountQueries.php",
      data: ajaxData,
      success: function (response) {
        if (response === "ok") {
          deleteButtonClickCounter = 0;
          // Refresh the entire student dashboard view
          rowClicked(selectedStudentId, type);
        } else {
          showError(response);
        }
      },
      error: handleAjaxError
    });
  } else {
    deleteButtonClickCounter++;
  }
}
