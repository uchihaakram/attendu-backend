<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Tahoma, Arial, sans-serif;
            background: #f4f4f4;
            padding: 20px;
            direction: rtl;
        }

        .container {
            background: #ffffff;
            max-width: 600px;
            margin: auto;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e0e0e0;
        }

        .header {
            background: #1e3a5f;
            color: #fff;
            padding: 20px;
            text-align: center;
        }

        .body {
            padding: 25px;
            color: #333;
            line-height: 1.8;
        }

        .stats {
            background: #fff8e1;
            border-right: 4px solid #f5a623;
            padding: 12px 16px;
            margin: 15px 0;
            border-radius: 4px;
        }

        .footer {
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #999;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>إشعار غياب أكاديمي</h2>
        </div>
        <div class="body">
            <p>عزيزي الطالب/ة <strong>{{ $warning->student->first_name }} {{ $warning->student->last_name }}</strong>،
            </p>

            <p>نحيطكم علمًا بأنه تم تسجيل تجاوز في نسبة الغياب الخاصة بمقرر
                <strong>{{ $warning->course->course_name }}</strong>.
            </p>

            <div class="stats">
                عدد المحاضرات التي تم رصد غيابك عنها: <strong>{{ $absentCount }}</strong>
                @if($maxAllowed)
                    من أصل <strong>{{ $maxAllowed }}</strong> غياب مسموح به كحد أقصى.
                @endif
            </div>

            @if($warning->warning_reason)
                <p><strong>ملاحظات إضافية:</strong> {{ $warning->warning_reason }}</p>
            @endif

            <p>نأمل منكم الانتظام في الحضور تجنبًا لأي إجراءات أكاديمية قد تُتخذ بحقكم مستقبلًا وفقًا للائحة الكلية.</p>

            <p>للاستفسار، يرجى التواصل مع شؤون الطلاب.</p>
        </div>
        <div class="footer">
            هذه رسالة تلقائية من نظام AttendU — لا يلزم الرد عليها.
        </div>
    </div>
</body>

</html>
