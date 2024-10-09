/*
 *
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 *  @category  BSS
 *  @package   Bss_ProductLabel
 *  @author    Extension Team
 *  @copyright Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 *  @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
require(
    [
        'fabricjs'
    ],
    function () {
        // disable Magento form autofocus
        jQuery('#edit_form').prepend('<input type="text" autofocus="autofocus" style="opacity: 0" />');

        var controlConfig = {mt: false, mb: false, ml: false, mr: false, mtr: false}; // disable rotation
        var canvas = this.__canvas = new fabric.Canvas('canvas');
        var objData = {};
        setCanvasBackground();

        document.getElementById('label_image').onchange = function (event) {
            var reader = new FileReader();
            reader.onload = function (event) {
                resetCanvas();
                var imgObj = new Image();
                imgObj.src = event.target.result;
                imgObj.onload = function () {
                    var width = this.width;
                    var height = this.height;
                    var image = new fabric.Image(imgObj);
                    //scale 50% of canvas
                    image.scaleToWidth(width);
                    image.setControlsVisibility(controlConfig);
                    canvas.centerObject(image);
                    canvas.add(image);
                    canvas.renderAll();
                    setImageData();
                }
            }
            reader.readAsDataURL(event.target.files[0]);
        };

        canvas.on(
            {
                'object:moving': setImageData,
                'object:scaling': setImageData,
                'object:rotating': setImageData,
                'object:modified': setImageData
            }
        );

        function setImageData()
        {
            var objects = canvas.getObjects();
            var obj = objects[0];
            if (!obj) {
                return;
            }
            var angle = obj.get('angle');

            var objWidth = obj.get('width') * obj.scaleX;
            var objWidthPercent = objWidth / canvas.width * 100;

            var objHeight = obj.get('height') * obj.scaleY;
            var objHeightPercent = objHeight / canvas.height * 100;

            var bound = obj.getBoundingRect();
            var objLeft = obj.get('left') / canvas.width * 100;
            var objTop = obj.get('top') / canvas.height * 100;
            objData = {
                left: objLeft,
                top: objTop,
                width: objWidthPercent,
                height: objHeightPercent,
                widthOrigin: parseInt(objWidth),
                heightOrigin: parseInt(objHeight),
                angle: angle
            };

            document.getElementById('label_image_data').value = JSON.stringify(objData);
        }

        function resetCanvas()
        {
            canvas.clear();
            setCanvasBackground();
        }

        function setCanvasBackground()
        {
            fabric.Image.fromURL(
                document.getElementById('placeholder_img').value,
                function (img) {
                    // add background image
                    canvas.setBackgroundImage(
                        img,
                        canvas.renderAll.bind(canvas),
                        {
                            scaleX: canvas.width / img.width,
                            scaleY: canvas.height / img.height
                        }
                    );
                }
            );
        }

        //========= CHECK IF EDIT ACTION & SET DATA
        var labelImage = document.getElementById('label_image_image');
        let childLabel;
        if (labelImage !== null) {
            childLabel = labelImage.src.replace('original_photo/', '');
            var http = new XMLHttpRequest();
            http.open('HEAD', childLabel, false);
            http.send();
            if (http.status !== 404) {
                labelImage.src = childLabel;
            }

            var labelImageData = JSON.parse(document.getElementById('label_image_data').value);
            fabric.Image.fromURL(
                labelImage.src + '?' + new Date().getTime(),
                function (labelImage) {
                    //i create an extra var for to change some image properties
                    var img = labelImage.set(
                        {
                            left: labelImageData.left * canvas.width/100,
                            top: labelImageData.top * canvas.height/100,
                            width: labelImageData.widthOrigin,
                            height: labelImageData.heightOrigin,
                            angle: labelImageData.angle
                        }
                    );
                    canvas.add(img);
                    img.setControlsVisibility(controlConfig);
                }
            );
        }

    }
);

