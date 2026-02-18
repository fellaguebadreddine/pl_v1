<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>جدول رقم 02</title>

    <style>
        body {
            font-family: "Tahoma", Arial, sans-serif;
            margin: 40px;
        }

        .header {
            text-align: center;
            line-height: 1.8;
            margin-bottom: 30px;
        }

        .top-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .top-info div {
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            vertical-align: middle;
            font-size: 13px;
        }

        th {
            font-weight: bold;
        }

        .big-cell {
            height: 60px;
        }

        .footer {
            margin-top: 30px;
            text-align: left;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="top-info">
        <div>جدول رقم: 02</div>
        <div>وثيقة الطعن: ..........</div>
    </div>

    <div class="header">
        <div>الجمهورية الجزائرية الديمقراطية الشعبية</div>
        <div>الأجهزة الاستشارية الداخلية</div>
        <div>لجان الطعن</div>
    </div>

    <table>

        <thead>

            <!-- Niveau 1 -->
            <tr>
                <th rowspan="3">السلك أو الرتبة</th>

                <th colspan="4">لجان الموظفين</th>

                <th colspan="4">لجان الطعن</th>

                <th rowspan="3">الملاحظات</th>
            </tr>

            <!-- Niveau 2 -->
            <tr>
                <!-- لجان الموظفين -->
                <th rowspan="2">مرجع القرار المتعلق بالإقصاء</th>
                <th rowspan="2">حدود الصلاحيات</th>
                <th colspan="2">التمديد</th>

                <!-- لجان الطعن -->
                <th rowspan="2">المرجع</th>
                <th rowspan="2">حدود الصلاحيات</th>
                <th colspan="2">التمديد</th>
            </tr>

            <!-- Niveau 3 -->
            <tr>
                <!-- تمديد الموظفين -->
                <th>المرجع</th>
                <th>الحدود</th>

                <!-- تمديد الطعن -->
                <th>المرجع</th>
                <th>الحدود</th>
            </tr>

        </thead>

        <tbody>

            <!-- Empty rows -->
            <tr>
                <td class="big-cell"></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>

            <tr>
                <td class="big-cell"></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>

            <tr>
                <td class="big-cell"></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>

        </tbody>

    </table>

    <div class="footer">
        مدير المؤسسة أو الإدارة المعنية
    </div>

</body>

</html>