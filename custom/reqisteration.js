$(document).ready(function () {
    // Global variables for calculations
    var totalAmount = 0;
    var options = {
        style: 'currency',
        // currency: 'IQD', 
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    };

    // Helper: Format Currency
    function formatIQD(amount) {
        return Number(amount).toLocaleString('en-US').split('.')[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ' د.ع';
    }

    // Helper: Parse Currency
    function regexToGetOnlyNumFromCurrencyAndCommas(value) {
        if (!value) return 0;
        let stringVal = String(value);
        let match = stringVal.match(/(\d{1,3}(?:(?:,\d{3})+|\d*))$/);
        if (!match) return 0;
        return Number(match[1].replace(/,/g, ''));
    }

    // 1. Handle Study Type Change
    $('#studentStudyTypeSelect').change(function (e) {
        e.preventDefault();

        // Hide all specific sections first
        $('#studentStageBachelorWrapper').slideUp();
        $('#studentStageHighWrapper').slideUp();
        $('#remarksWrapper').slideUp();
        $("#reg-data-container").html('<div class="text-center p-5"><div class="spinner-border text-info" role="status"></div></div>');

        let val = $(this).val();

        if (val == 1) { // Bachelor
            $('#studentStageBachelorWrapper').slideDown();
            $('#remarksWrapper').slideDown();

            $("#reg-data-container").load("includes/acceptChanelBachelor.php", function () {
                getAllAcceptTypes(); // Load dropdown data
            });

        } else if (val == 0) {
            $("#reg-data-container").html('<div class="text-center p-4 text-muted"><i class="fa fa-arrow-right fa-3x mb-3"></i><p>يرجى اختيار نوع الدراسة</p></div>');
        } else { // High Studies
            $('#studentStageHighWrapper').slideDown();
            $('#remarksWrapper').slideDown();

            $("#reg-data-container").load("includes/acceptChanelHigh.php", function () {
                // High studies might not strictly need an init call if options are static, 
                // but checking connection is good.
            });
        }
    });

    // ---------------------------------------------------------
    // Bachelor Logic (Delegated Events)
    // ---------------------------------------------------------

    // Fetch Accept Types for Bachelor
    function getAllAcceptTypes() {
        $('#acceptChanelSelect').html('<option id="0" value="0" selected>نوع القبول</option>');
        $.ajax({
            type: "post",
            url: "php_action/RegisQueries.php",
            data: { GetAllAcceptsStudent: "GetAllAcceptsStudent" },
            dataType: "json",
            success: function (response) {
                if (response) {
                    response.forEach(element => {
                        $('#acceptChanelSelect').append(`<option id="${element.accept_type_id}" value="${element.amount}">${element.name}</option>`);
                    });
                }
            },
            error: function (err) {
                console.error(err);
                showError("فشل في تحميل أنواع القبول");
            }
        });
    }

    // Bachelor Channel Change
    $(document).on('change', '#acceptChanelSelect', function (e) {
        e.preventDefault();
        $('#bachChanelDetail').fadeIn();
        resetAcceptTypeInputs();
        totalAmount = 0;

        let selectedId = $(this).find('option:selected').attr('id');
        let selectedAmount = $(this).val();

        if (selectedId == 0) {
            resetAcceptTypeInputs();
            $('#bachChanelDetail').hide();
        } else {
            // Unlock button
            $('#addBachStudentButton').prop("disabled", false);

            totalAmount = Number(selectedAmount);
            $('#acceptTypeAmountInput').val(formatIQD(totalAmount));

            // If amount is 0 (Free?), allow manual edit? Logic from original file:
            if (totalAmount === 0) {
                $('#acceptTypeAmountInput').prop("disabled", false);
            } else {
                $('#acceptTypeAmountInput').prop("disabled", true);
            }
        }
    });

    // Bachelor Amount Change (Manual override)
    $(document).on('change', '#acceptTypeAmountInput', function (e) {
        e.preventDefault();
        let val = $(this).val();
        if (val.includes("د.ع") || val.includes(",")) {
            totalAmount = regexToGetOnlyNumFromCurrencyAndCommas(val);
        } else {
            totalAmount = Number(val) || 0;
        }
        $(this).val(formatIQD(totalAmount));
    });

    function resetAcceptTypeInputs() {
        $('#acceptTypeAmountInput').val("");
        $('#addBachStudentButton').prop("disabled", true);
    }

    // Add Bachelor Student
    $(document).on('click', '#addBachStudentButton', function (e) {
        e.preventDefault();

        let studentName = $('#studentNameInput').val().trim();
        let remarks = $('#StudentRemarksinput').val();
        let stage = $('#studentStageBachelor').val();
        let acceptTypeId = $('#acceptChanelSelect option:selected').attr("id");

        // Validation
        if (!studentName || studentName.length < 3) {
            showWarning('يرجى كتابة اسم الطالب بشكل صحيح');
            return;
        }
        if (!stage || stage == 0) {
            showWarning('يرجى اختيار المرحلة الدراسية');
            return;
        }
        if (!acceptTypeId || acceptTypeId == 0) {
            showWarning('يرجى اختيار نوع القبول');
            return;
        }

        const formData = new FormData();
        formData.append("total", Number(totalAmount));
        formData.append("discountPercentage", 0);
        formData.append("studentStageBachelor", stage);
        formData.append("accept_type_id", acceptTypeId);
        formData.append("RemainInput", Number(totalAmount)); // Initially remain = total
        formData.append("studentRemarks", remarks);
        formData.append("studentNameInput", studentName);

        $.ajax({
            url: "php_action/RegisQueries.php",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                // Response should be the new ID or error
                if (!isNaN(response)) {
                    showSuccess(`تم إضافة الطالب: ${studentName} (ID: ${response})`);

                    // Reset Form
                    $('#studentNameInput').val("");
                    $('#StudentRemarksinput').val("");
                    $('#studentStageBachelor').val(0);
                    $('#acceptChanelSelect').val(0).trigger('change');
                } else {
                    showError("حدث خطأ: " + response);
                }
            },
            error: function (xhr, status, error) {
                showError("خطأ في الاتصال: " + error);
            }
        });
    });


    // ---------------------------------------------------------
    // High Studies Logic (Delegated Events)
    // ---------------------------------------------------------

    // High Channel Select (Public/Private)
    $(document).on('change', '#HighChanelSelect', function (e) {
        e.preventDefault();
        let val = $(this).val(); // 1=Public, 2=Private

        $('#HighChanelDetail').hide();
        resetAcceptTypeInputs();

        if (val == 0) return;

        $('#HighChanelDetail').fadeIn();

        if (val == 1) { // Public (General)
            $('#addHighStudentButton').prop('disabled', false);
            totalAmount = 0;
            $('#acceptTypeAmountInput').val(formatIQD(0));
        } else if (val == 2) { // Private (Special)
            // Fetch amount based on diploma/master/phd
            let id_high = $('#studentStudyTypeSelect option:selected').attr('id-high');

            $.ajax({
                type: "post",
                url: "php_action/RegisQueriesHigh.php",
                data: { acceptTypeHighIdToGetAmount: id_high },
                dataType: "json",
                success: function (response) {
                    totalAmount = Number(response.amount);
                    $('#acceptTypeAmountInput').val(formatIQD(totalAmount));
                    $('#addHighStudentButton').prop('disabled', false);

                    if (totalAmount === 0) {
                        $('#acceptTypeAmountInput').prop("disabled", false);
                    } else {
                        $('#acceptTypeAmountInput').prop("disabled", true);
                    }
                },
                error: function () {
                    showError('فشل في جلب تفاصيل الرسوم');
                }
            });
        }
    });

    // Add High Student
    $(document).on('click', '#addHighStudentButton', function (e) {
        e.preventDefault();

        let studentName = $('#studentNameInput').val().trim();
        let remarks = $('#StudentRemarksinput').val();
        let stage = $('#studentStageHigh').val();
        let channel = $('#HighChanelSelect').val(); // 1 or 2
        let acceptTypeHighId = $('#studentStudyTypeSelect option:selected').attr('id-high');

        // Validation
        if (!studentName || studentName.length < 3) {
            showWarning('يرجى كتابة اسم الطالب بشكل صحيح');
            return;
        }
        if (!stage || stage == 0) {
            showWarning('يرجى اختيار المرحلة الدراسية');
            return;
        }
        if (channel == 0) {
            showWarning('يرجى اختيار قناة القبول (عام/خاص)');
            return;
        }

        const formData = new FormData();

        // High Specific Fields
        formData.append("UniversityComand", $("#UniversityComand").val());
        formData.append("DateOfLaunch", $("#DateOfLaunch").val());
        formData.append("StudyCert", $("#StudyCert").val());
        formData.append("TheOnlyCert", $("#TheOnlyCert").val());

        if (channel == 1) { // Public
            // Public
            formData.append("IsPublic", "yes");
            formData.append("studentNameInput1", studentName);
            formData.append("studentStageBachelor1", stage);
            formData.append("studentRemarks1", remarks);
            formData.append("accept_type_high_id1", acceptTypeHighId);
        } else { // Private
            // Private
            formData.append("studentNameInput", studentName);
            formData.append("studentRemarks", remarks);
            formData.append("studentStageBachelor", stage);
            formData.append("total", Number(totalAmount));
            formData.append("discountPercentage", 0);
            formData.append("accept_type_high_id", acceptTypeHighId);
            formData.append("RemainInput", Number(totalAmount));
        }

        $.ajax({
            url: "php_action/RegisQueriesHigh.php",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (!isNaN(response)) {
                    showSuccess(`تم إضافة الطالب: ${studentName} (ID: ${response})`);
                    // Reset
                    $('#studentNameInput').val("");
                    $('#StudentRemarksinput').val("");
                    // Clear high fields
                    $('#UniversityComand, #DateOfLaunch, #StudyCert, #TheOnlyCert').val("");
                    $('#HighChanelSelect').val(0).trigger('change');
                } else {
                    showError("حدث خطأ: " + response);
                }
            },
            error: function (xhr, status, error) {
                showError("خطأ في الاتصال: " + error);
            }
        });

    });

});
