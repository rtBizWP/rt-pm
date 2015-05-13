/** ====================================================================
 * jsPDF Cell plugin
 * Copyright (c) 2013 Youssef Beddad, youssef.beddad@gmail.com
 *               2013 Eduardo Menezes de Morais, eduardo.morais@usp.br
 *               2013 Lee Driscoll, https://github.com/lsdriscoll
 *               2014 Juan Pablo Gaviria, https://github.com/juanpgaviria
 *               2014 James Hall, james@parall.ax
 *               2014 Diego Casorran, https://github.com/diegocr
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * ====================================================================
 */

(function (jsPDFAPI) {
    'use strict';
    /*jslint browser:true */
    /*global document: false, jsPDF */

    var fontName,
        fontSize,
        fontStyle,
        padding = 3,
        margin = 13,
        headerFunction,
        lastCellPos = { x: undefined, y: undefined, w: undefined, h: undefined, ln: undefined },
        pages = 1,
        setLastCellPosition = function (x, y, w, h, ln) {
            lastCellPos = { 'x': x, 'y': y, 'w': w, 'h': h, 'ln': ln };
        },
        getLastCellPosition = function () {
            return lastCellPos;
        },
        NO_MARGINS = {left:0, top:0, bottom: 0};

    jsPDFAPI.setHeaderFunction = function (func) {
        headerFunction = func;
    };

    jsPDFAPI.getTextDimensions = function (txt) {
        fontName = this.internal.getFont().fontName;
        fontSize = this.table_font_size || this.internal.getFontSize();
        fontStyle = this.internal.getFont().fontStyle;
        // 1 pixel = 0.264583 mm and 1 mm = 72/25.4 point
        var px2pt = 0.264583 * 72 / 25.4,
            dimensions,
            text;

        text = document.createElement('font');
        text.id = "jsPDFCell";
        text.style.fontStyle = fontStyle;
        text.style.fontName = fontName;
        text.style.fontSize = fontSize + 'pt';
        text.textContent = txt;

        document.body.appendChild(text);

        dimensions = { w: (text.offsetWidth + 1) * px2pt, h: (text.offsetHeight + 1) * px2pt};

        document.body.removeChild(text);

        return dimensions;
    };

    jsPDFAPI.cellAddPage = function () {
        var margins = this.margins || NO_MARGINS;

        this.addPage();

        setLastCellPosition(margins.left, margins.top, undefined, undefined);
        //setLastCellPosition(undefined, undefined, undefined, undefined, undefined);
        pages += 1;
    };

    jsPDFAPI.cellInitialize = function () {
        lastCellPos = { x: undefined, y: undefined, w: undefined, h: undefined, ln: undefined };
        pages = 1;
    };

	jsPDFAPI.getBase64Image = function (url) {
    var img = new Image();
    img.src = url;
    img.onload = function () {
    var canvas = document.createElement("canvas");
    canvas.width =this.width;
    canvas.height =this.height;
    var ctx = canvas.getContext("2d");
    ctx.drawImage(this, 0, 0);
    var dataURL = canvas.toDataURL("image/png");
	console.log(dataURL);
    return dataURL; 
    };
}

    jsPDFAPI.cell = function (x, y, w, h, txt, ln, align) {
        var curCell = getLastCellPosition();

        // If this is not the first cell, we must change its position
        if (curCell.ln !== undefined) {
            if (curCell.ln === ln) {
                //Same line
                x = curCell.x + curCell.w;
                y = curCell.y;
            } else {
                //New line
                var margins = this.margins || NO_MARGINS;
                if ((curCell.y + curCell.h + h + margin) >= this.internal.pageSize.height - margins.bottom) {
                    this.cellAddPage();
                    if (this.printHeaders && this.tableHeaderRow) {
                        this.printHeaderRow(ln, true);
                    }
                }
                //We ignore the passed y: the lines may have diferent heights
                y = (getLastCellPosition().y + getLastCellPosition().h);

            }
        }
		var padding_new = padding;
		var txt_new = [];
		if(txt.className == 'rt_project_resources_title'){
		    var imgData = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAMCAgICAgMCAgIDAwMDBAYEBAQEBAgGBgUGCQgKCgkICQkKDA8MCgsOCwkJDRENDg8QEBEQCgwSExIQEw8QEBD/2wBDAQMDAwQDBAgEBAgQCwkLEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBD/wAARCAEAAQADASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD9U6KKKACiis698R+HtNlMOo69p1rIvVJ7pEYfgTQBo0VzsnxG+HsPE3jvw6n+9qkA/wDZqif4ofDSNd0nxE8MqPU6tbgf+h0AdPRXK/8AC1/hb/0Urwr/AODm2/8Ai6lHxN+G5GR8QfDRB7/2tb//ABdAHS0VzX/CzPhv/wBFB8Nf+DaD/wCLo/4WZ8N/+ig+Gv8AwbQf/F0AdLRWVo/ivwv4hkkh0DxJpWpyRKGkSzvI5igPQkITgVq0AFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABXyX+2d/wUN+Gf7KUUnhTTrdPFfxBmiEkWiwzbIrJWGVku5BnywRyIxl2GPuqQ1bn7fH7Wlr+yh8GZNY0h4JvGniNn07w5ayYYLLtzJdOp6pCpBx0LtGp4Ykfz+67rus+J9ZvfEXiLVLnUtU1Kd7q7u7mUySzyucs7seSSTnNAHuPxq/bv/ai+O15cN4q+KOp6ZpczEpo2hStp9lGp/gKxkNKB6ys5968CklkmkaaaRpJHJZmY5LE9SSepptFABRRRQAUUUUAFFFFAGhoHiLxB4V1WDXfC+uaho+pWrboLywuXt54j6rIhDKfoa+3P2c/+Ctvx7+F97aaN8XpB8RfDKsscr3W2PVYI/wC9HcAASkcnEwYt03r1r4TooA/pu+DHxs+G/wAfvAll8RPhh4hi1TSrsbXH3Z7WYAFoZ4+sci5GVPYggkEE91X86f7Gv7WPi79k/wCKtp4o064uLrwvqUkdt4j0gP8AJd2uceYq9BNHksjfVScMa/ob8O+INH8WeH9N8UeHb+K90vV7SK+srmI5SaCRA6OPYqQaANGiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKAPwB/4KWfHa4+N37VHiSG2uWbQ/BMjeGNLjz8ubd2FxJjpl5/N57qqelfKtd/8AtC/8l9+Jf/Y4az/6Wy1wFABRRRQAUUUUAFFFFABRRRQAUUUUAFft7/wSA+Ml18Q/2bLn4f6tdGa/+HuptYQljlvsE4MsGT7N5yD0VFr8Qq+rv2Cv23LH9jXU/GE2q+BbrxNZ+LIbFDHb3y27QPbNMQ3zIwbInYdugoA/fqivzUh/4Le/C1gPtHwO8VIe+zUbZv5gVbT/AILc/Bkgb/g140B74uLQ/wDs9AH6QUV+cw/4LbfAskbvhF47A7kNZn/2rUn/AA+0+AX/AESj4gf982X/AMfoA/RWivzuT/gtl+z2V/efCz4hqfRYrEj/ANKBTv8Ah9l+zx/0S74i/wDfix/+SaAP0Por89F/4LYfs3lRv+GfxJB7gWtgR/6VUv8Aw+v/AGbf+iafEr/wEsP/AJKoA/Qqivz1/wCH1/7Nv/RNPiV/4CWH/wAlVKP+C1X7NGOfh98SP/AKy/8AkqgD9BaK/Pr/AIfVfsz/APRPviR/4BWX/wAlVIn/AAWn/ZkYZfwL8RkOehsLM/yuaAP0Bor4BT/gtH+zAzYbwX8RFHqdPtP/AJJqwn/BZz9ldgN/hn4gpnrnTLY4/K4oA+9qK+GbP/gsX+yNcuFuYPG9qD1aTRkYD67ZSa98+C/7ZX7Nvx+ljsfht8UdLutUlHy6VeFrO+OOu2GYKz49U3D3oA9qooooA/mW/aF/5L78S/8AscNZ/wDS2WuAru/j4Sfjp8RiTknxZq//AKWS1wlABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAU+3uJ7SeO6tZ5IZomDxyRsVZGByCCOQQe9MooA/SD9hj/gqZ4o8F6rp3ws/aS1qfWvDFwyWtl4luDvu9MJOF+0v1mh6Zc5deuWHA/YK0u7W/tYb6xuYri2uI1lhmicMkiMMqysOCCCCCK/ldr9hP8Agj/+1Ve+OPCmofs3eNdTe41TwpbfbvDs0zZaXTNwWS3yevkuy7e+yTAwI6APy2+Pf/JdPiL/ANjZq/8A6WS1wld38e/+S6fEX/sbNX/9LJa4SgD3r9iz9lvUv2svjPbfD9NQfTdE0+3bVNdvkAMkNmjqpWMHgyOzqi54GSxBCkV+3vhD9iH9k/wVolpoel/Ajwjcx2kYj+0ajpsd5cykfxSSyhmZieck/TA4r89/+CINqrfEj4n3pUbotDsYgfZrhyf/AEAV+vNAHmKfswfs3RgBPgJ8Phjp/wAU5af/ABup1/Zt/Z4Uhh8CPh7kdP8AimbL/wCN16PRQB55/wAM6/s+/wDRCvh7/wCExZf/ABqj/hnX9n3/AKIV8Pf/AAmLL/41XodFAHBf8KA+A/8A0RPwF/4Tdn/8bo/4UB8B/wDoifgL/wAJuz/+N13tFAHBf8KA+A//AERPwF/4Tdn/APG6P+FAfAf/AKIn4C/8Juz/APjdd7RQBwX/AAoD4D/9ET8Bf+E3Z/8Axuo3/Z5+AEjbpPgb8PmPTLeGbIn/ANF16DRQB55/wzr+z7/0Qr4e/wDhMWX/AMao/wCGdf2ff+iFfD3/AMJiy/8AjVeh0UAeef8ADOv7Pv8A0Qr4e/8AhMWX/wAao/4Z1/Z9/wCiFfD3/wAJiy/+NV6HRQB55/wzr+z7/wBEK+Hv/hMWX/xqj/hnX9n3/ohXw9/8Jiy/+NV6HRQB5y37N/7PLtub4EfD0k/9SzZf/G6T/hmz9nf/AKIR8Pf/AAmbL/43Xo9FAHnH/DNn7O//AEQj4e/+EzZf/G6P+GbP2d/+iEfD3/wmbL/43Xo9FAHnH/DNn7O//RCPh7/4TNl/8bo/4Zs/Z3/6IR8Pf/CZsv8A43Xo9FAHnH/DNn7O/wD0Qj4e/wDhM2X/AMbo/wCGbP2d/wDohHw9/wDCZsv/AI3Xo9FAHj/jD9kL9mTxv4cvvC+sfA7wZDa38RjeWw0eCzuI/Ro5olV0YHkEH8xX4cftr/so6z+yV8X5vBr3M2oeG9ViOoeHtRkXDT2pbBjkwMebGflbHX5WwAwFf0T1+UP/AAXH/wCQl8IP+uGtf+hWlAH5a17n+w14y1TwN+118J9W0q6eB7zxRY6RMQcB4L2UWsqn1BSY/oe1eGV6P+zXMbf9ov4V3AOPK8a6G+c46X0JoAp/Hv8A5Lp8Rf8AsbNX/wDSyWuEruPjrIZfjd8QpSMF/FWrNj63ctcPQB+n3/BDyMHxV8WJefl0/Sl/OS4/wr9aq/Jz/gh1/wAh34t/9emj/wDod1X6x0AFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFflH/AMFx0b7f8IHxwYdbH4hrP/Gv1cr8p/8AguT/AMfHwd/3Nc/nZUAflfXffs+sqfHv4auxwF8X6MT/AOBsVcDXdfAUgfHP4dEnAHizSP8A0sioAi+OH/JafH//AGNGq/8ApXJXFV2vxw/5LT4//wCxo1X/ANK5K4qgD9SP+CHKA6t8XZOci20ZfwLXf+FfrBX5Q/8ABDj/AJCXxf8A+uGi/wDoV3X6vUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFflP/AMFyf+Pj4O/7mufzsq/Vivyn/wCC5P8Ax8fB3/c1z+dlQB+V9dr8D/8AktPgD/saNK/9K464qu1+B/8AyWnwB/2NGlf+lcdAB8cP+S0+P/8AsaNV/wDSuSuKrtPjcyt8Z/HzKcg+KNVIPqPtclcXQB+p3/BDdFN38YZD1EeiD8Cbz/Cv1ar8p/8Aght/x8fGL/c0P+d7X6sUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFflX/wXKVcfBt8fMf7eBPt/oNfqpX5W/wDBcr/V/Bv/AHte/wDbGgD8qa7P4JuIvjL4CkbOE8T6Wxx7XcdcZXYfBr/kr/gb/sZNM/8ASqOgA+Mv/JX/ABz/ANjJqf8A6VSVx9dh8Zf+Sv8Ajn/sZNT/APSqSuPoA/VD/ghuD5/xiODjZoQz+N7X6sV+Vn/BDX7nxk+ug/8At9X6p0AFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFflb/AMFyv9X8G/8Ae17/ANsa/VKvyt/4Llf6v4N/72vf+2NAH5U12Hwa/wCSv+Bv+xk0z/0qjrj67D4Nf8lf8Df9jJpn/pVHQAfGX/kr/jn/ALGTU/8A0qkrj66/4xsr/F3xw6nIbxJqZB9vtUlchQB+qv8AwQ1+58ZB76D/AO31fqnX5Wf8ENfufGT66D/7fV+qdABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABX5Xf8Fylbyfg0+PlLa+M++LGv1Rr8sf8AguX/AMeXwY/66+IP5afQB+Utdd8H3EXxa8EykZCeI9NbH0uY65Gur+Ev/JVPBn/Ywad/6Ux0AHxa/wCSqeM/+xg1H/0pkrlK6v4tf8lU8Z/9jBqP/pTJXKUAfqr/AMENfufGT66D/wC31fqnX5W/8ENf9X8ZP97Qf/b6v1SoAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAK/LH/guX/x5fBj/AK6+IP5afX6nV+WP/Bcv/jy+DH/XXxB/LT6APylrq/hL/wAlU8Gf9jBp3/pTHXKV1fwl/wCSqeDP+xg07/0pjoAPi1/yVTxn/wBjBqP/AKUyVyldd8YIxF8W/G0S5ITxHqSjPtcyVyNAH6q/8ENfufGT66D/AO31fqnX5Uf8ENmbzvjEu44K6Ece+b2v1XoAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAK/K//guW5Nv8Go8cB9fb8xYf4V+qFflb/wAFyv8AV/Bv/e17/wBsaAPyprq/hL/yVTwZ/wBjBp3/AKUx1yldX8Jf+SqeDP8AsYNO/wDSmOgCb4zKV+MHjpWGCPEupgj/ALepK46u6+PUXk/HP4iw7ceX4s1dcemLyWuFoA/Uz/ghw5F98YI8DBh0Rvya8/xr9XK/J3/ghzLjWvi5DkfNa6O2O/D3X+NfrFQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAV+VX/BcmRSfg5Fzkf2834H7D/hX6q1+Uf/BceTOofCCHI+WHWmx35az/AMKAPyzrq/hL/wAlU8Gf9jBp3/pTHXKV2PwYhFx8YfAtuekviXS0P43UYoA3f2orFtN/aW+LOnuu02/jjXUA9hfzY/TFeY19Q/8ABSv4bal8OP2xvHYurV47PxNcR+I9PlK4E8VygaRh9JxOn1Q18vUAfpR/wRG8SafZ/FP4j+FZ7lUvNT0K1vLeMnBkWCcrJj1I89OPTPoa/YCv5iPhB8WvG3wN+Imj/E/4e6n9i1vRZvMhZl3RyoQVeKRf4kdSVYeh4wcGv1d8Cf8ABa34JXuhWrfEn4Z+MdJ1nYBdJpEdveWpfuyNJNE4U9cFSR0yetAH6NUV8Lw/8Fj/ANkaUAvY+Poc9n0WE4/75nNTp/wWH/ZAY4ZvGye7aIv9JaAPuGiviH/h8J+x7/z38af+CMf/ABypE/4K/fsdMu5tQ8XofRtCOf0egD7aor4n/wCHvv7HH/QU8Xf+CJv/AIuj/h77+xx/0FPF3/gib/4ugD7Yor4n/wCHvv7HH/QU8Xf+CJv/AIuj/h77+xx/0FPF3/gib/4ugD7Yor4n/wCHvv7HH/QU8Xf+CJv/AIuj/h77+xx/0FPF3/gib/4ugD7Yor4n/wCHvv7HH/QU8Xf+CJv/AIuj/h77+xx/0FPF3/gib/4ugD7Yor4n/wCHvv7HH/QU8Xf+CJv/AIuj/h77+xx/0FPF3/gib/4ugD7Yor4n/wCHvv7HH/QU8Xf+CJv/AIuj/h77+xx/0FPF3/gib/4ugD7Yor4n/wCHvv7HH/QU8Xf+CJv/AIuj/h77+xx/0FPF3/gib/4ugD7Yor4n/wCHvv7HH/QU8Xf+CJv/AIuj/h77+xx/0FPF3/gib/4ugD7Yor4lf/gr9+x0q7l1Dxe59F0I5/V6j/4fCfse/wDPfxp/4Ix/8coA+3qK+Hn/AOCw/wCyApwreNn910Rf6y1BN/wWP/ZGiBKWPj6bHZNFhGf++pxQB90V+Qn/AAW78Sabd/En4aeFILlXvtN0a9vbiIHPlpPMixk+hPkP+VexeOv+C1vwQstDum+HHwz8ZatrOwi2TV47aytd/Yu8c0r7R1wFyemR1H5R/GL4u+N/jr8RdY+KHxC1IXmtazMJJSi7Y4Y1G2OGNf4URQFA9BySSSQDjK9F/Zvsn1L9of4XadGu5rrxpokIHqWvoR/WvOq+m/8Agm78M9R+Jn7YfgGK1tHks/DV4fEt/KFysEVoN8bN9Z/IQe7igD9TP+Ckv7Htx+0/8KIde8FWaP4+8GCW60pOFOoWzAGazJ/vNtDR543rt4DsR+D15Z3enXc+n6hazW11bSNDPBMhSSKRSQyMp5VgQQQeQRX9UVfEf7cP/BNTwd+0rNcfEb4bXVj4U+IhXNxJJGVsdZIHH2kICUl7CZQSRwytwVAPwzor0j4wfs4/G34D6zPo3xT+HOs6KYWwt41uZLKcdmiuUzE4+jZHQgHivN6ACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiivR/hD+zp8bPjvrMGi/Cz4c6zrbTMFa7S3MdnAO7S3L4ijH+8wz0GTxQB57aWl1f3UNjY20txc3EixQwxIXeR2OFVVHJJJAAHXNfu9/wAE1v2PLn9mP4VT+JPG9ksfj7xosVxqcZALadaqCYbPP94bi8mON5C87ATi/sPf8E0fCH7Ns9t8R/ibdWPiv4hqoa2aOMtY6MSOfs4cAyS/9NmAI6KF5Lfb9ABRRRQAhAIKsAQeOawL74e+AdUcy6l4H8P3bk5LT6ZDIT+LKa6CigDlf+FUfC3/AKJr4V/8E1t/8RR/wqj4W/8ARNfCv/gmtv8A4iuqooA5X/hVHwt/6Jr4V/8ABNbf/EUf8Ko+Fv8A0TXwr/4Jrb/4iuqooA5X/hVHwt/6Jr4V/wDBNbf/ABFH/CqPhb/0TXwr/wCCa2/+IrqqKAOV/wCFUfC3/omvhX/wTW3/AMRR/wAKo+Fv/RNfCv8A4Jrb/wCIrqqKAOV/4VR8Lf8AomvhX/wTW3/xFH/CqPhb/wBE18K/+Ca2/wDiK6qigDlf+FUfC3/omvhX/wAE1t/8RR/wqj4W/wDRNfCv/gmtv/iK6qigDlf+FUfC3/omvhX/AME1t/8AEUf8Ko+Fv/RNfCv/AIJrb/4iuqooA5X/AIVR8Lf+ia+Ff/BNbf8AxFH/AAqj4W/9E18K/wDgmtv/AIiuqooA5X/hVHwt/wCia+Ff/BNbf/EUf8Ko+Fv/AETXwr/4Jrb/AOIrqqKAOV/4VR8Lf+ia+Ff/AATW3/xFH/CqPhb/ANE18K/+Ca2/+IrqqKAOV/4VR8Lf+ia+Ff8AwTW3/wARR/wqj4W/9E18K/8Agmtv/iK6qigDlf8AhVHwt/6Jr4V/8E1t/wDEUf8ACqPhb/0TXwr/AOCa2/8AiK6qigDn7H4e+AdLcS6b4H8P2jjndBpkMZz9VUVvgBQFUAAdAKWigAooooA//9k=';//this.getBase64Image('http://subsite.voximulti.com/wp-content/themes/voxxiboss-child-theme/images/user_employee-128.png');
			this.addImage(imgData,'jpeg', x + padding, y + 5 ,10,10);
			txt_new = this.splitTextToSize(String(txt.textContent.replace(/\r?\n/g, '')), w - padding - 10);
			txt = txt_new;
			padding_new = padding + 14 ;
		}else if(txt.className == 'rt_project_assignee'){
			var imgData = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAMCAgICAgMCAgIDAwMDBAYEBAQEBAgGBgUGCQgKCgkICQkKDA8MCgsOCwkJDRENDg8QEBEQCgwSExIQEw8QEBD/2wBDAQMDAwQDBAgEBAgQCwkLEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBD/wAARCACAAIADASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD9U6KKKACiiigAoorxT9p39rj4QfspeFk1z4jas82qXqMdK0Ky2vfX7DglVJASMH70jkKOgyxCkA9ror8I/jx/wVX/AGn/AIt3lzZ+DNcX4ceH3YiKz0Jv9MKdjJesPM3+8XlD/Zr5/tvBf7Svxtzrdn4T+Jnj7ziWN3FYX+qbyTyd4V88+9AH9LlFfzRzaF+0r8CJE1a50b4mfDx42BS5ktr/AEkq3bDkJg/jX0l8Af8AgrP+0f8ACy8ttO+JV3F8SPDqlUli1PEWoxJ3Md2gyzd/3yyZ6ZHWgD9yqK8o/Z2/ab+Ev7UHg0eMPhdrvntb7U1HTLoCO+02VgcJPFk4zg4dSUbB2scHHq9ABRRRQAUUUUAFFFFABRRRQB5L+1H+0P4X/Zg+Des/FTxIi3MtsBa6Vp+/Y2oX8gPlQA9hwzMRnaiO2DjB/A4t8dP22vj2ATc+KPG/i66O0ElYLaEZOBnIht4k/BVHcnn6p/4LKfGm88XfHjSfg1Y3jf2V4E02Oe5hVuG1G8VZWZgOu2D7OBnkb39TX1b/AMEjv2bdO+GnwNHxs1rT0Pij4hhpIJZE+e10lHIhjX081lMzEfeUw5+5QB1/7K3/AATK+BfwB0yz1vxro9j4+8cBVkn1HU7cS2dpJ3W1tnyqhTjEjhpCRkFM7R9iIiRoscaKqqAqqowAB0AFOooAjnt4LuCS2uoY5oZlKSRyKGV1IwQQeCCO1fEf7Wn/AAS1+Dnxs0u98TfCTTNP8A+OVVpYjZxeVpeoSddk8CDbEWP/AC1iAIJJZZOlfcFFAH83HgXxx8bv2Jvjy9/aQXXh7xb4WuzZ6rpd1nyruHIL28yg4khkXaysCRgo6HIVq/oG+Anxr8I/tC/CjQPiz4LkIsdat90ts7hpbO5U7ZreTH8SOCM9CMMOGBr4f/4LG/s26d4l+G1h+0h4e05I9b8KSw6drjxrg3OmzPsid8dWimdFB/uzNk4VceVf8EVfjTeaZ458YfATUrtjp+tWX/CRaXG7fLHeQFIp1UeskTxsfa2/MA/XaiiigAooooAKKKKACiiigD+c79u3VLrV/wBsL4t3d2zM8fia6tQT12QkRIP++UWv3/8Agzoll4a+EHgfw7pqKtppnhvTLOAL0CR20ar+gr8Lf+CmfgS68CftnePVkhKW2vy22u2jkY8xLiBDIw+kyzL/AMBr9lv2KvihY/F/9lr4ceMLW5WW4XQ7fTNQAbLLe2i/Z5ww6jLxFgD/AAsp7g0Ae3UUUUAFFFFAHkX7XuiWXiD9lf4uabqCK0X/AAhesXC7uiyRWkksbfg6Kfwr8V/+CZGp3Ol/tv8Aw1a2ZsXM2o2sqj+JH065Bz7A4P4V+tf/AAUe+KFj8Lv2PvH1zPcrHeeJrL/hGLCInBmkvMxyKPpB57/RDX5gf8EkPAl14t/bG0fxBHAWtvB2kajq87EfKN8JtEGfXddAgf7JPagD92aKKKACiiigAooooAKKKKAPzx/4K/8A7Ml98SPhrpvx68I6c1xrPgKJ4NYjiXLzaO7bjJgcnyJCXPoksrH7tfJH/BMj9tuw/Zw8Y3fww+JeomD4f+LblJftbklNH1HAQXDekUihUkPbYjcBWz+ufxx/aO+A3wF0WSb4y+PtH0lLqFtmmSn7Rd3kZyCEtUDSSKfuk7dvPJFfz7ftGan8C9b+K+s6z+zvpmv6Z4OvpTNb2GsQxxtbSEnekOx3/c55QMdyg4PSgD+lW0u7W/tYb6wuorm2uY1mhmhcOkiMMqysOCCCCCOCDU1fz6fsw/8ABQv9oD9mGCDw5ouqQeJvB8ROPD+tFpIoATk/ZpQfMg7/ACgmPJJKE8194+EP+C2nwQvrOM+O/hL420a8IG9NLe01CEH/AH5JIGx/wCgD9Gqp6xrGk+HtKu9d17U7XTtN0+B7m7u7qVYoYIkGWd3YgKoAJJJxX50+Nf8Agtt8HrKyk/4V38H/ABhrF5tIQazPbafCG7EmJ52I9sD8K+Av2nP27/j7+1KzaV4y1yHSPCyyCSLw5o4aGzJByrTEkvOw4PzsVBGVVaAO1/4KN/tmx/tT/Eu30PwVcTL8PvCDSxaSXUodRuGwJb1lPIBACxq3IQEkKXZR+hH/AASi/Zkvvgl8ELj4j+LtOa18T/Ecw33kSriS10yMN9ljYH7rPveVh6PGCAVr8jP2aPGPwY8AfF7RfGHx38Fat4q8M6VILn+zdPki/eXCkGNpY5MLNGpyTHvQMQASV3K371/Af9sX9nb9oyKOD4Y/ESxn1YpvfRL7/RNRjwMnEEmDIAOrRl1HrQB7VRRRQAUUUUAFFFISAMk4AoAr6lqWnaNp9zq2r39vY2NlE9xc3NzKscUMSAszu7EBVABJJOABX5Uftj/8Fc9QuLq/+HX7K0i29rGWt7rxjPDukmPQ/YYnGEX/AKbOCTk7VXCufL/+Clf7fGofG7xLffBH4Ua08Pw70W4MOoXdtJj+37uNuWLDrbIw+RejkeYc/Jt0f2A/+CZV58a7Sw+Mfx5t7vTfA022fStGRmhutbXqJXYfNFbHsRh5BypVdrMAfLvwm/Z9/aQ/a+8X3l/4P0DWvFV5cT51TxDqlw32eKQ4ybi7mOC+OduWcjopr9Dfgx/wRT8E6ZBBqXx5+Jt/rd5gNJpfh1Ra2qN3VriVWklX3VIjX6PeFvCnhnwP4fsvCvg3QLDRNH06MQ2ljYW6wwQoOyooAHqfUkk1rUAfnP8Att/sI/sbfBv9lvxd8Q/Dvw1m0XWPDtlGumXlvrN48kl3NMkMQkWWR1kUvIpYFc4BwVFfmv8Asjfs0ar+1f8AGGD4U6Z4jXQEOn3WpXWpNZm6FvFEoAPlB03bpHjT7wxvzzjB/Sz/AILVfET+wvgb4O+G1vPsn8V+IGvZVB+/bWUR3KR6ebcQH/gNeb/8EQvh3vvvib8WbmDHlQ2fh2ylx13lp7lc+2y1P40Afn1+0V8FdV/Z4+NHif4OaxqY1Kfw5cxxLerB5AuoZIklilEZZtu5JFONxx6mv2D/AGav2BP2JvF3wC8JeLR8KE14+LtBstSub3VNSuJboSSwqzorxuixFHLKfLVfu96+R/8AgtR8Nv8AhH/jl4Q+JtrBst/F2gtZTMB9+7spMMxPr5U9uP8AgFfXX/BIb4lf8Jt+yXb+FLm433fgfWrzSdrHLfZ5WF1E30zPIg/6547UAcJ8Yf8Agi98G/EcM998GPHWt+Dr8gmOy1H/AImVgT2UE7ZkB6Fi8mP7p7/nX8f/ANjH9o/9lTUF1jxr4Yn/ALKt5la18T6JK89iJN3yHzlCvA+fuiRUYkcZ61/RXVfUNPsNWsbjTNVsbe8s7uNoZ7e4iWSKWNhhkdWyGUg4IIwRQB+Nv7IX/BWjx/8ADi5sfA37Rkt34w8K5WCPXQN+raevQNIf+XuMd9373qdz4CH9gPBvjPwp8Q/DGneNPBGv2etaHq0IuLO+tJA8UyH0PYgggqcEEEEAgivzB/by/wCCV9nYWGpfGL9l7R3RbdXu9W8HQgt8g5eWwHXgZJt+eM+X0WM/L37BX7cHiX9lDx3HoniG6ur/AOG2u3KrrWm8ubJzhfttuvaRRjeo/wBYowfmCFQD9+aKp6PrGl+IdIste0PUIL/TtSt47uzurdw8U8MihkkRhwVZSCCOxq5QAV8Zf8FTv2j7v4Gfs8P4V8NX7Wvif4iyS6NaSRvtkt7JVBvJlPY7HSIEcgzhhytfZtfiJ/wWN8fXPib9qi18Gidvsng7w9aWqxZ4W4uC1xI/1KSQA+yCgDjv+CbH7JFt+038ZH1fxlYGbwJ4JEV9q8bKdl/cMT9ns8/3WKsz/wCwhXgupr96IIILWCO2toUhhhQRxxxqFVFAwFAHAAHavk3/AIJb/C+z+G/7HvhXUBbLHqPjKS48RXz7cF/NcpBz1wIIofbJb1r62oAKKKKAPxN/4LKfET/hJ/2nNM8C28+638F+HreGWPOdl3dM07n2zE1t+Vfev/BKr4d/8IF+xt4ZvpoPKu/F17e+IbgYwSJJPJhPvmC3hb8a/Hv9s3xHr3iz9qr4o674k02+0+7m8S3cUdtewtFNHbRN5VsGRgCP3CRY9sY4xX7JfsA/tXfBP41/CPwz8OPBN1/YniPwbodnpl14cvZF+0iK3hSLz4WGBPEduSyjIJ+dVyMgHCf8Fh/ht/wl/wCyxD42trfddeB9dtb55AMlbW4zbSL9DJLbsf8Acr5h/wCCJ/xJ/sf4ueOvhXcz7YfEuiQ6rbqx4NxZS7Sqj1Md07H2i9hX1t/wUm/at+CHw2+DXi34JeJLv+3vF/i/RprG30SxkUyWRlT91dXLciFUbbIqkF3KjAxll/K7/gn54n1/wl+2J8MNU8PabfX8s+sjT7qCzheV/slzG0E8jKoJKRxytIx6AR57UAf0R0UUUAFfjL/wVp/ZBsPhX4ytv2g/h/pa23hvxjdtBrdrBHiOy1Ygv5qgfdScB2I6CRH5+dQP2arx79r74XWfxj/Zo+IngO6tlmnutDuLqwyuSt7br59uR3H72NAcdiR3oA+Pv+CN37R934x8Ca3+zv4nv2nv/By/2noTSPl20yV9ssI74hmZSPa4VRgKK/SKv57/APgm94+ufh/+2X8OrqOdkt9bvZNAukzgSpdxNEin6TGJvqgr+hCgAr8B/wDgqfa3Nv8AtzfEOWdGVLmLRpYSf4kGlWiEj/gSMPwNfvxX5R/8FoP2edVfU/Dn7Snh/T3msFtU8PeIWjTP2d1dmtZ3x/C/mPEWOACsQ6sKAPu79iPU7LV/2RfhFdWDq0UfhHTrVip48yGIRSD670YfWvbq/G//AIJt/wDBRbwt8DfDafAf44S3Fr4WS6kn0PXIommXTTK5eSCeNQW8kyFnDqCVZ2yCpyv62eCPiT8PfiXpi6z8PfHGheJLJ1DefpWoRXKgH+9sY7T6g4I70AdJRRRQB87/ALW37Efwl/a08PlfEdqNF8XWcJj0vxLZwg3MHUiOZeBPDk/cYgjJ2MhJJ/Er40/AT49fsXfFC0g8RpfaHqVnObnQfEmkzyJBdhD/AK22nXBDDI3IcOuRuUAjP9Htcl8UfhT8PfjR4NvfAPxO8LWevaHfD95bXCnKOAdskbjDRyLk4dCGGeDQB/P3+zv+y78c/wBsvx/dDw1HdXML3Xna/wCKtWkke3tmc7maWU5aaZs5EYJdicnC5Yft3+yx+xx8If2TvDH9m+B9N+3+ILyILqviO9jU3t6eCVB/5Yw5AxEvHALFmyx9V8BeAPBfwv8AClh4H+H3hqx0HQtMj8u1srOPYiDux7sxPLOxLMSSSSSa6GgAooooAKxvGep2Wi+ENd1jUmVbOw025ubhmPAjSJmYn2wDTvE3i7wp4L019a8Y+JtJ0LT4wS93qV7HawqPd5CFH51+Z3/BQr/gpj8Pte+H+t/Aj9nzVzrtx4ggfT9b8RQqy2kNm/EsFuxwZnkXKM4GwIx2licqAfn3+yFa3N5+1Z8HYrRGZ18d6FKQOuxL6J3P4KrGv6TK/E7/AIJDfs86r8QvjwfjZqenuPDXw9jkMM7r8lxqs0RSKJc9THG7ytj7pEWfvCv2xoAKzPEvhrQPGXh/UPCnirR7XVdH1a3e0vbK6jEkU8LjDIynqCK06KAPyU/aR/4I0eJrTU7zxJ+zL4ktNQ0yVmlXw3rdwYbm3z/yzguiCkq9h5uwgDl3PJ+KfFX7Jf7WPwpvzNrfwQ8d6bLbE/6bYadNcwoR3Fxbb4/yav6QaKAP5qU+MX7UHg7/AEWP4p/FLQ9ny+WNc1G22+2N4xTh+1b+1LF+7H7SfxUTbxtHjHURj/yNX9KlFAH81P8Aw0z+1FcqAP2gvinKGPA/4SvUWyf+/tO/4X1+1Jct/wAln+KkrAf9DFqLED/v5X9KlFAH81Z+O/7UlsQ7fGX4pxnsT4i1Ff8A2pSf8NKftR2u5f8Ahf3xTixyw/4SrUV/P97X9KtFAH81f/DV/wC1Lt2f8NKfFTbjGP8AhMdRxj/v9TW+Ov7UPib/AEST4xfFLVgfl8o+IdRnz7Y8w1/StRQB/Npof7OX7U/xa1BLnTPhB8RNfnm4+23OlXRj/wCBXEqhB+LCvrf4Af8ABG34veK7+01f4/a/ZeC9FDB59MsJ473VJl7puTdBDkfx7pCP7lfspRQBynwv+F3gX4NeB9M+HXw38P2+jaDpMey3t4sksTy0jsfmeRjks7Ekk811dFFAH//Z';//this.getBase64Image('http://subsite.voximulti.com/wp-content/themes/voxxiboss-child-theme/images/user_employee-128.png');
			this.addImage(imgData,'jpeg', x + padding + 10 , y + 5 ,10,10);
			txt_new = this.splitTextToSize(String(txt.innerText.replace(/\r?\n/g, '')), w - padding - 30);
			txt = txt_new;
			padding_new = padding + 24 ;
		}else if(txt.className == 'rt_project_tasks'){
			var imgData = 'data:image/jpeg;base64,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';//this.getBase64Image('http://subsite.voximulti.com/wp-content/themes/voxxiboss-child-theme/images/user_employee-128.png');
			this.addImage(imgData,'jpeg', x + padding + 10 , y + 5 ,10,10);
			txt_new = this.splitTextToSize(String(txt.innerText.replace(/\r?\n/g, '')), w - padding - 30);
			txt = txt_new;
			padding_new = padding + 24 ;
		}else if(typeof txt != 'string'){
			txt_new = this.splitTextToSize(String(txt.innerText.replace(/\s+\r?\n/g, '')), w - padding);
			txt = txt_new;
		}
        if (txt[0] !== undefined) {
            if (this.printingHeaderRow) {
                this.rect(x, y, w, h, 'FD');
            } else {
                this.rect(x, y, w, h);
            }
            if (align === 'right') {
                if (txt instanceof Array) {
                    for(var i = 0; i<txt.length; i++) {
                        var currentLine = txt[i];
                        var textSize = this.getStringUnitWidth(currentLine) * this.internal.getFontSize();
                        this.text(currentLine, x + w - textSize - padding, y + this.internal.getLineHeight()*(i+1));
                    }
                }
            } else {
                this.text(txt, x + padding_new, y + this.internal.getLineHeight());
            }
        }
        setLastCellPosition(x, y, w, h, ln);
        return this;
    };

    /**
     * Return the maximum value from an array
     * @param array
     * @param comparisonFn
     * @returns {*}
     */
    jsPDFAPI.arrayMax = function (array, comparisonFn) {
        var max = array[0],
            i,
            ln,
            item;

        for (i = 0, ln = array.length; i < ln; i += 1) {
            item = array[i];

            if (comparisonFn) {
                if (comparisonFn(max, item) === -1) {
                    max = item;
                }
            } else {
                if (item > max) {
                    max = item;
                }
            }
        }

        return max;
    };

    /**
     * Create a table from a set of data.
     * @param {Integer} [x] : left-position for top-left corner of table
     * @param {Integer} [y] top-position for top-left corner of table
     * @param {Object[]} [data] As array of objects containing key-value pairs corresponding to a row of data.
     * @param {String[]} [headers] Omit or null to auto-generate headers at a performance cost

     * @param {Object} [config.printHeaders] True to print column headers at the top of every page
     * @param {Object} [config.autoSize] True to dynamically set the column widths to match the widest cell value
     * @param {Object} [config.margins] margin values for left, top, bottom, and width
     * @param {Object} [config.fontSize] Integer fontSize to use (optional)
     */

    jsPDFAPI.table = function (x,y, data, headers, config) {
        if (!data) {
            throw 'No data for PDF table';
        }

        var headerNames = [],
            headerPrompts = [],
            header,
            i,
            ln,
            cln,
            columnMatrix = {},
            columnWidths = {},
            columnData,
            column,
            columnMinWidths = [],
            j,
            tableHeaderConfigs = [],
            model,
            jln,
            func,

        //set up defaults. If a value is provided in config, defaults will be overwritten:
           autoSize        = false,
           printHeaders    = true,
           fontSize        = 12,
           margins         = NO_MARGINS;

           margins.width = this.internal.pageSize.width;

        if (config) {
        //override config defaults if the user has specified non-default behavior:
            if(config.autoSize === true) {
                autoSize = true;
            }
            if(config.printHeaders === false) {
                printHeaders = false;
            }
            if(config.fontSize){
                fontSize = config.fontSize;
            }
            if(config.margins){
                margins = config.margins;
            }
        }

        /**
         * @property {Number} lnMod
         * Keep track of the current line number modifier used when creating cells
         */
        this.lnMod = 0;
        lastCellPos = { x: undefined, y: undefined, w: undefined, h: undefined, ln: undefined },
        pages = 1;

        this.printHeaders = printHeaders;
        this.margins = margins;
        this.setFontSize(fontSize);
        this.table_font_size = fontSize;

        // Set header values
        if (headers === undefined || (headers === null)) {
            // No headers defined so we derive from data
            headerNames = Object.keys(data[0]);

        } else if (headers[0] && (typeof headers[0] !== 'string')) {
            var px2pt = 0.264583 * 72 / 25.4;

            // Split header configs into names and prompts
            for (i = 0, ln = headers.length; i < ln; i += 1) {
                header = headers[i];
                headerNames.push(header.name);
				var trimmed_header = jQuery.trim(header.prompt);
                headerPrompts.push(trimmed_header);
                columnWidths[header.name] = header.width *px2pt;
            }

        } else {
            headerNames = headers;
        }

        if (autoSize) {
            // Create a matrix of columns e.g., {column_title: [row1_Record, row2_Record]}
            func = function (rec) {
                return rec[header];
            };

            for (i = 0, ln = headerNames.length; i < ln; i += 1) {
                header = headerNames[i];

                columnMatrix[header] = data.map(
                    func
                );

                // get header width
                columnMinWidths.push(this.getTextDimensions(headerPrompts[i] || header).w);
                column = columnMatrix[header];

                // get cell widths
                for (j = 0, cln = column.length; j < cln; j += 1) {
                    columnData = column[j];
                    columnMinWidths.push(this.getTextDimensions(columnData).w);
                }

                // get final column width
                columnWidths[header] = jsPDFAPI.arrayMax(columnMinWidths);
            }
        }

        // -- Construct the table

        if (printHeaders) {
            var lineHeight = this.calculateLineHeight(headerNames, columnWidths, headerPrompts.length?headerPrompts:headerNames);

            // Construct the header row
            for (i = 0, ln = headerNames.length; i < ln; i += 1) {
                header = headerNames[i];
                tableHeaderConfigs.push([x, y, columnWidths[header], lineHeight, String(headerPrompts.length ? headerPrompts[i] : header)]);
            }

            // Store the table header config
            this.setTableHeaderRow(tableHeaderConfigs);

            // Print the header for the start of the table
            this.printHeaderRow(1, false);
        }

        // Construct the data rows
        for (i = 0, ln = data.length; i < ln; i += 1) {
            var lineHeight;
            model = data[i];
			//$.each( model, function( key, value ) {
			//	var model_text = model[key];
			//	model[key] = jQuery.trim(model_text);
			//  });
            lineHeight = this.calculateLineHeight(headerNames, columnWidths, model);

            for (j = 0, jln = headerNames.length; j < jln; j += 1) {
                header = headerNames[j];
                this.cell(x, y, columnWidths[header], lineHeight, model[header], i + 2, header.align);
            }
        }
        this.lastCellPos = lastCellPos;
        this.table_x = x;
        this.table_y = y;
        return this;
    };
    /**
     * Calculate the height for containing the highest column
     * @param {String[]} headerNames is the header, used as keys to the data
     * @param {Integer[]} columnWidths is size of each column
     * @param {Object[]} model is the line of data we want to calculate the height of
     */
    jsPDFAPI.calculateLineHeight = function (headerNames, columnWidths, model) {
        var header, lineHeight = 0, tempheader = [];
        for (var j = 0; j < headerNames.length; j++) {
            header = headerNames[j];
			if(typeof model[header] == 'object'){
				tempheader[header] = this.splitTextToSize(String(model[header].innerText), columnWidths[header] - padding);
			}else{
				tempheader[header] = this.splitTextToSize(String(model[header]), columnWidths[header] - padding);
			}
            var h = this.internal.getLineHeight() * tempheader[header].length + padding;
            if (h > lineHeight)
                lineHeight = h;
        }
        return lineHeight;
    };

    /**
     * Store the config for outputting a table header
     * @param {Object[]} config
     * An array of cell configs that would define a header row: Each config matches the config used by jsPDFAPI.cell
     * except the ln parameter is excluded
     */
    jsPDFAPI.setTableHeaderRow = function (config) {
        this.tableHeaderRow = config;
    };

    /**
     * Output the store header row
     * @param lineNumber The line number to output the header at
     */
    jsPDFAPI.printHeaderRow = function (lineNumber, new_page) {
        if (!this.tableHeaderRow) {
            throw 'Property tableHeaderRow does not exist.';
        }

        var tableHeaderCell,
            tmpArray,
            i,
            ln;

        this.printingHeaderRow = true;
        if (headerFunction !== undefined) {
            var position = headerFunction(this, pages);
            setLastCellPosition(position[0], position[1], position[2], position[3], -1);
        }
        this.setFontStyle('bold');
        var tempHeaderConf = [];
        for (i = 0, ln = this.tableHeaderRow.length; i < ln; i += 1) {
            this.setFillColor(200,200,200);

            tableHeaderCell = this.tableHeaderRow[i];
            if (new_page) {
                tableHeaderCell[1] = this.margins && this.margins.top || 0;
                tempHeaderConf.push(tableHeaderCell);
            }
            tmpArray = [].concat(tableHeaderCell);
            this.cell.apply(this, tmpArray.concat(lineNumber));
        }
        if (tempHeaderConf.length > 0){
            this.setTableHeaderRow(tempHeaderConf);
        }
        this.setFontStyle('normal');
        this.printingHeaderRow = false;
    };

})(jsPDF.API);
