<select id="HighChanelSelect" class=" py-2 px-4 bg-white " required="required">
    <option value="0">اختر نوع القبول</option>
    <option value="1">عام</option>
    <option value="2">خاص</option>
</select>


<select id="acceptChanelSelect" class=" py-2 px-4 bg-white my-3" style="display: none;" required="required">

</select>



<div id="HighChanelDetail" style="display: none;" class="d-flex flex-column  justify-content-center text-center">


    <div class="d-flex mt-3">

        <p class="w-25">المبلغ الكلي</p>
        <input class="w-75" type="text" id="acceptTypeAmountInput" disabled></input>


    </div>












    <div class="d-flex my-3">

        <p class=" w-25">الامر الجامعي</p>
        <input class="w-75" placeholder="رقم + تاريخ" type="text" id="UniversityComand"></input>


    </div>



    <div class="d-flex ">

        <p class=" w-25">تاريخ المباشرة</p>
        <input class="w-75 form-control" type="date" id="DateOfLaunch"></input>

    </div>


    <div class="d-flex my-3">

        <p class=" w-25">الاجازة الدراسية</p>
        <input class="w-75" type="text" id="StudyCert"></input>

    </div>


    <div class="d-flex ">

        <p class=" w-25">الاجازة</p>
        <input class="w-75" type="text" id="TheOnlyCert"></input>

    </div>



    <button id="addHighStudentButton" style="font-size: 26px;" type="button"
        class="btn btn-info w-100 py-2 mt-4 btn-block" disabled>اضافة الطالب</button>




</div>