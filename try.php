<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <table border="1" cellpadding="8" cellspacing="0">
    <tr>
        <th>Criteria</th>
        <th>Developing (0–10)</th>
        <th>Accomplished (11–15)</th>
    </tr>

    <!-- Row 1 -->
    <tr>
        <td>Articulate requirements</td>

        <td>
            <input type="radio" name="articulate_select" onclick="
                document.getElementById('art_dev').disabled=false;
                document.getElementById('art_acc').disabled=true;
                document.getElementById('art_acc').value='';
            "> 
            <input type="number" id="art_dev" min="0" max="10" disabled>
        </td>

        <td>
            <input type="radio" name="articulate_select" onclick="
                document.getElementById('art_acc').disabled=false;
                document.getElementById('art_dev').disabled=true;
                document.getElementById('art_dev').value='';
            ">
            <input type="number" id="art_acc" min="11" max="15" disabled>
        </td>
    </tr>

    <!-- Row 2 -->
    <tr>
        <td>Choose appropriate tools and methods</td>

        <td>
            <input type="radio" name="tools_select" onclick="
                document.getElementById('tools_dev').disabled=false;
                document.getElementById('tools_acc').disabled=true;
                document.getElementById('tools_acc').value='';
            ">
            <input type="number" id="tools_dev" min="0" max="10" disabled>
        </td>

        <td>
            <input type="radio" name="tools_select" onclick="
                document.getElementById('tools_acc').disabled=false;
                document.getElementById('tools_dev').disabled=true;
                document.getElementById('tools_dev').value='';
            ">
            <input type="number" id="tools_acc" min="11" max="15" disabled>
        </td>
    </tr>

    <!-- Row 3 -->
    <tr>
        <td>Give clear and coherent presentation</td>

        <td>
            <input type="radio" name="presentation_select" onclick="
                document.getElementById('pres_dev').disabled=false;
                document.getElementById('pres_acc').disabled=true;
                document.getElementById('pres_acc').value='';
            ">
            <input type="number" id="pres_dev" min="0" max="10" disabled>
        </td>

        <td>
            <input type="radio" name="presentation_select" onclick="
                document.getElementById('pres_acc').disabled=false;
                document.getElementById('pres_dev').disabled=true;
                document.getElementById('pres_dev').value='';
            ">
            <input type="number" id="pres_acc" min="11" max="15" disabled>
        </td>
    </tr>
</table>

</body>
</html>