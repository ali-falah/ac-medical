var HighData = []
var BachData = []

function formatIQD(amount) {
  return Number(amount).toLocaleString("en-US").split('.')[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",") + " د.ع";
}

function handleAjaxError(jqXHR, textStatus, errorMessage) {
  console.error("AJAX Error:", errorMessage);
  showError("حدث خطأ في الاتصال: " + errorMessage);
}

function regexToGetOnlyNumFromCurrencyAndCommas(value) {
    return String(value).replace(/[^0-9.]/g, "");
}

selectedStudentId = 0;
MainSelectedType = 0;
SelectedStudentBachShouldnotBeUpdated = false;
counterDeleteStuden = 1;

$(document).ready(function() {
    $('#students_table').html("");
    getAllAcceptTypes();
    
    // Initial fetch of all students
    GetAllBachStudents();
    GetAllHighStudents();

    $('#SearchStudentInput').keyup(function(e) {
        e.preventDefault();
        const val = this.value.toLowerCase();
        renderFilteredStudents(val);
    });

    $('#TimesToCloseEditView, #drawerBackdrop').click(function(e) {
        e.preventDefault();
        $('#EditStudentView').removeClass('active');
        $('body').removeClass('drawer-open');
    });

    $('#AcceptTypeBach').change(function(e) {
        e.preventDefault();
        if (SelectedStudentBachShouldnotBeUpdated == false) {
            $("#AmountInputAfterChangeAcceptType").val(formatIQD(this.value));
        }
    });

    $('#HighIsPublic').change(function(e) {
        e.preventDefault();
        $('#AmountInputAfterChangeAcceptType').val("0 د.ع");
    });


    $('#AcceptChanelHigh').change(function(e) {
        $('#AmountInputAfterChangeAcceptType').val("0 د.ع");
        IsPubSelect = $('#HighIsPublic').val();
        if (Number(IsPubSelect == 1)) { //عام
            $('#AmountInputAfterChangeAcceptType').val("0 د.ع");
        } else { //خاص 
            e.preventDefault();
            $.ajax({
                type: "post",
                url: "php_action/RegisQueriesHigh.php",
                data: {
                    acceptTypeHighIdToGetAmount: Number(this.value)
                },
                dataType: "json",
                success: function(response) {
                    $('#AmountInputAfterChangeAcceptType').val(formatIQD(response.amount));
                }
            });
        }
    });


    $('#EditStudentButton').click(function(e) {
        e.preventDefault();
        if (SelectedStudentBachShouldnotBeUpdated == false) {

            studentName = $('#StudentName').val();
            RemarksInput = $('#RemarksInput').val();




            if (MainSelectedType == 1) {
                //bach with amount and accept type



                BachStage = $('#BachStageSelect').val();
                amountBach = regexToGetOnlyNumFromCurrencyAndCommas($('#AmountInputAfterChangeAcceptType').val());
                AcceptTypeBach = Number($('#AcceptTypeBach option:selected').attr("id"));
             


                $.ajax({
                    type: "post",
                    url: "php_action/EditStudent.php",
                    data: {
                        BachStudentIdToEditWithAmountAndAcceptType: selectedStudentId,
                        studentName: studentName,
                        BachStage: BachStage,
                        amountBach: amountBach,
                        RemarksInput: RemarksInput,
                        AcceptTypeBach: AcceptTypeBach
                    },
                    dataType: "text",
                    success: function(response) {
                       
                            $('#EditStudentView').removeClass('active');
                            $('body').removeClass('drawer-open');

                            GetAllBachStudents()
                            GetAllHighStudents()
                            $('#students_table').html("");
                        
                    }
                });




            }else{
                 //high ajax
                 
                
                 HighStage = $('#HighStageSelect').val();
                 UniCommandInput = $('#UniCommandInput').val();
                 DateOfLunchInput = $('#DateOfLunchInput').val();
                 StudyCertInput = $('#StudyCertInput').val();
                 TheOnlyCertInput = $('#TheOnlyCertInput').val();

                 amountHigh = regexToGetOnlyNumFromCurrencyAndCommas($('#AmountInputAfterChangeAcceptType').val());
                 AcceptTypeHigh = Number($('#AcceptChanelHigh').val());

                 


                 IsPublic = 0 
                 if(Number($('#HighIsPublic').val())==1){
                    IsPublic = 1 ;
                 }

                 $.ajax({
                     type: "post",
                     url: "php_action/EditStudent.php",
                     data: {
                         HighStudentIdToEditWithAmountAndAcceptType: selectedStudentId,
                         StudyCertInput: StudyCertInput,
                         studentName: studentName,
                         HighStage: HighStage,
                         UniCommandInput: UniCommandInput,
                         RemarksInput: RemarksInput,
                         DateOfLunchInput: DateOfLunchInput,
                         TheOnlyCertInput: TheOnlyCertInput, IsPublic :IsPublic ,
                         amountHigh:amountHigh  , AcceptTypeHigh:AcceptTypeHigh
                     },
                     dataType: "text",
                     success: function(response) {
                      
                             $('#EditStudentView').removeClass('active');
                             $('body').removeClass('drawer-open');
                             GetAllBachStudents()
                             GetAllHighStudents()
                             $('#students_table').html("");
                         
                     }
                 });



            }


        } else {
            //updateOnly Name and stage and remarks and high extra options
            studentName = $('#StudentName').val();
            RemarksInput = $('#RemarksInput').val();
            BachStage = $('#BachStageSelect').val();

            if (MainSelectedType == 1) {
                //bach ajax 


                $.ajax({
                    type: "post",
                    url: "php_action/EditStudent.php",
                    data: {
                        BachStudentIdToEditWithOutAmountAndAcceptType: selectedStudentId,
                        studentName: studentName,
                        BachStage: BachStage,
                        RemarksInput: RemarksInput
                    },
                    dataType: "text",
                    success: function(response) {

                            
                                                        $('#EditStudentView').removeClass('active');
                            $('body').removeClass('drawer-open');

                            GetAllBachStudents()
                            GetAllHighStudents()
                            $('#students_table').html("");
                        
                    }
                });


               

            } else {
                //extra options without accept type and amount and chanel
                //high ajax
                studentName = $('#StudentName').val();
                RemarksInput = $('#RemarksInput').val();
                HighStage = $('#HighStageSelect').val();
                UniCommandInput = $('#UniCommandInput').val();
                DateOfLunchInput = $('#DateOfLunchInput').val();
                StudyCertInput = $('#StudyCertInput').val();
                TheOnlyCertInput = $('#TheOnlyCertInput').val();

                $.ajax({
                    type: "post",
                    url: "php_action/EditStudent.php",
                    data: {
                        HighStudentIdToEditWithOutAmountAndAcceptType: selectedStudentId,
                        StudyCertInput: StudyCertInput,
                        studentName: studentName,
                        HighStage: HighStage,
                        UniCommandInput: UniCommandInput,
                        RemarksInput: RemarksInput,
                        DateOfLunchInput: DateOfLunchInput,
                        TheOnlyCertInput: TheOnlyCertInput
                    },
                    dataType: "text",
                    success: function(response) {

                            console.log(response);
                                                        $('#EditStudentView').removeClass('active');
                            $('body').removeClass('drawer-open');

                            GetAllBachStudents()
                            GetAllHighStudents()
                            $('#students_table').html("");
                        
                    }
                });


               


            }

        }
    });



    $('#DeleteStudentButton').click(function (e) { 
        e.preventDefault();
        
        if(counterDeleteStuden===10){
            if(MainSelectedType==1){
                //delete bach student 

                $.ajax({
                    type: "post",
                    url: "php_action/EditStudent.php",
                    data: {
                        StudentBachIdToDelete:selectedStudentId
                    },
                    dataType: "text",
                    success: function (response) {
                        console.log(response);
                                                    $('#EditStudentView').removeClass('active');
                            $('body').removeClass('drawer-open');

                        GetAllBachStudents()
                        GetAllHighStudents()
                        $('#students_table').html("");
                    }
                });

            }else{// delete high
                
                $.ajax({
                    type: "post",
                    url: "php_action/EditStudent.php",
                    data: {
                        StudentHighIdToDelete:selectedStudentId
                    },
                    dataType: "text",
                    success: function (response) {
                        console.log(response);
                                                    $('#EditStudentView').removeClass('active');
                            $('body').removeClass('drawer-open');

                        GetAllBachStudents()
                        GetAllHighStudents()
                        $('#students_table').html("");
                    }
                });
            }
        }


        counterDeleteStuden++;
    });

});


function GetAllBachStudents() {
    return $.ajax({
        type: "post",
        url: "php_action/accountQueries.php",
        data: {
            getBachAllStudents: "getBachAllStudents"
        },
        dataType: "json",
        success: function(response) {
            BachData = response;
            renderFilteredStudents($('#SearchStudentInput').val().toLowerCase());
        }
    });
}

function GetAllHighStudents() {
    return $.ajax({
        type: "post",
        url: "php_action/accountQueries.php",
        data: {
            GetAllHighStudents: "GetAllHighStudents"
        },
        dataType: "json",
        success: function(response) {
            HighData = response;
            renderFilteredStudents($('#SearchStudentInput').val().toLowerCase());
        }
    });
}

function renderFilteredStudents(val = "") {
    $('#students_table').html("");
    
    const filteredBach = BachData.filter(obj => 
        String(obj.name || "").toLowerCase().includes(val) || 
        String(obj.std_id || "").includes(val) || 
        String(obj.remarks || "").toLowerCase().includes(val)
    );
    appendToTable(filteredBach, 1);

    const filteredHigh = HighData.filter(obj => 
        String(obj.name || "").toLowerCase().includes(val) || 
        String(obj.std_id || "").includes(val) || 
        String(obj.remarks || "").toLowerCase().includes(val)
    );
    appendToTable(filteredHigh, 2);
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
      <tr onclick="rowClicked(${element.std_id}, ${type})" class="row-std">
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
  const stages = ["", "اولى", "ثانية", "ثالثة", "رابعة", "خامسة", "سادسة"];
  return stages[stage] || stage;
}


function rowClicked(studentId, type) {
  selectedStudentId = studentId;
  MainSelectedType = type;
  $('#EditStudentView').addClass('active');
  $('body').addClass('drawer-open');
  
  // Reset all sections
  $('#HighStudentDetailsOnEditView, #bachFinancialSection, #BachStageSelectDiv, #HighStageSelectDiv').hide();
  $('#AmountDivAfterChangeAcceptType').hide();
  $('#HighStageSelect option:selected, #BachStageSelect option:selected').prop('selected', false);
  $('#AcceptChanelHigh, #HighIsPublic').prop('disabled', true);

  // Populate summary card
  $('#studentIdDisplay').text(studentId);
  $('#studyTypeDisplay').text(type === 2 ? 'دراسات عليا' : 'بكالوريوس');

  const ajaxUrl = "php_action/EditStudent.php";
  const ajaxData = type === 2 ? { studentHighIdToGetInfo: studentId } : { studentBachIdToGetInfo: studentId };

  $.ajax({
    type: "post",
    url: ajaxUrl,
    data: ajaxData,
    dataType: "json",
    success: function(response) {
      $('#StudentName').val(response.name);
      $('#RemarksInput').val(response.remarks);
      $('#AmountInputAfterChangeAcceptType').val(formatIQD(response.total_amount));

      if (type === 2) {
        // High student
        $('#HighStudentDetailsOnEditView').show();
        $('#HighStageSelectDiv').show();
        $('#HighStageSelect #' + response.stage).prop('selected', true);
        $('#AcceptChanelHigh #' + response.accept_type_high_id).prop('selected', true);
        $('#UniCommandInput').val(response.UniversityComand);
        $('#TheOnlyCertInput').val(response.TheOnlyCert);
        $('#StudyCertInput').val(response.StudyCert);
        $('#DateOfLunchInput').val(response.DateOfLaunch);

        if (Number(response.total_amount) != Number(response.total_remain)) {
          // Has payments - disable editing
          $('#AcceptChanelHigh, #HighIsPublic').prop('disabled', true);
          SelectedStudentBachShouldnotBeUpdated = true;
        } else {
          // No payments - allow editing
          $('#AcceptChanelHigh, #HighIsPublic').prop('disabled', false);
          SelectedStudentBachShouldnotBeUpdated = false;
          $('#AmountDivAfterChangeAcceptType').show();
        }

        if (Number(response.IsPublic) === 1) {
          $('#pubOption').prop('selected', true);
        } else {
          $('#PriOption').prop('selected', true);
        }
      } else {
        // Bach student
        $('#bachFinancialSection').show();
        $('#BachStageSelectDiv').show();
        $('#BachStageSelect #' + response.stage).prop('selected', true);
        $('#AcceptTypeBach #' + response.accept_type_id).prop('selected', true);

        if (Number(response.total_amount) == Number(response.total_remain)) {
          SelectedStudentBachShouldnotBeUpdated = false;
          $('#AmountDivAfterChangeAcceptType').show();
        } else {
          SelectedStudentBachShouldnotBeUpdated = true;
        }
      }
    },
    error: handleAjaxError
  });
}


function getAllAcceptTypes() { //for bach select accept types
    //acceptChanelSelect
    $('#AcceptTypeBach').html("");
    $('#AcceptTypeBach').append('<option id="0" value="0" selected>نوع القبول</option>')
    $.ajax({
        type: "post",
        url: "php_action/RegisQueries.php",
        data: { GetAllAcceptsStudent: "GetAllAcceptsStudent" },
        dataType: "json",
        success: function(response) {
            response.forEach(element => {
                $('#AcceptTypeBach').append('<option id="' + element.accept_type_id + '" value="' + element.amount + '">' + element.name + '</option>')
            });
        }
    });

}



function regexToGetOnlyNumFromCurrencyAndCommas(value) {
    if (!value) return 0;
    let stringVal = String(value);
    // Remove everything except numbers and dots
    return stringVal.replace(/[^0-9.]/g, "");
}