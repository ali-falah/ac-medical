$(document).ready(function () {
  $("#SearchStudentReportButton").click(function (e) {
    e.preventDefault();

    const $btn = $(this);
    const originalText = $btn.html();
    
    // Validation: at least one filter should be selected/filled? 
    // Actually, backend might allow "All" (0). Let's just ensure UI feedback.

    $btn.prop("disabled", true).html('<div class="spinner-border spinner-border-sm text-light" role="status"></div> جاري التوليد...');

    studentId = $("#IdStudentSearchInput").val();
    studentAcceptType = $("#SelectStudyTypeReport").val();
    studentPaidOrNot = $("#SelectAgeTypeReport").val();

    $.ajax({
      type: "post",
      url: "php_action/reportQuiries.php",
      data: {
        studentId: Number(studentId),
        studentAcceptType: Number(studentAcceptType),
        studentPaidOrNot: Number(studentPaidOrNot),
      },
      dataType: "text",
      success: function (response) {
        $btn.prop("disabled", false).html(originalText);
        
        if (!response || response.trim().length < 10) { // Basic check for empty/error response
             showWarning("لا توجد بيانات مطابقة للعرض");
             return;
        }

        var mywindow = window.open(
          "",
          "Stock Management System",
          "height=950,width=1200"
        );
        
        if (mywindow) {
            mywindow.document.write("<html><head><title>تقرير الطلاب</title>");
            mywindow.document.write("</head><body>");
            mywindow.document.write(response);
            mywindow.document.write("</body></html>");
            mywindow.document.close(); 
            mywindow.focus();
        } else {
            showError("تم حظر النوافذ المنبثقة. يرجى السماح بها لهذا الموقع.");
        }
      },
      error: function (xhr, status, error) {
          $btn.prop("disabled", false).html(originalText);
          showError("حدث خطأ أثناء توليد التقرير");
          console.error(error);
      }
    });
  });
});
