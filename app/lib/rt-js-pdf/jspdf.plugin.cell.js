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
			var imgData = 'data:image/jpeg;base64,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';//this.getBase64Image('http://subsite.voximulti.com/wp-content/themes/voxxiboss-child-theme/images/user_employee-128.png');
			this.addImage(imgData,'jpeg', x + padding + 10 , y + 5 ,10,10);
			txt_new = this.splitTextToSize(String(txt.innerText.replace(/\r?\n/g, '')), w - padding - 10);
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
