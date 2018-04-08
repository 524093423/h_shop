
        Date.prototype.format = function(partten)
        {
            if(partten ==null||partten=='')
            {
                partten = 'y-m-d'    ;
            }
            var y = this.getYear();
            var m = this.getMonth()+1;
            var d = this.getDate();
            var r = partten.replace(/y+/gi,y);
            r = r.replace(/m+/gi,(m<10?"0":"")+m);
            r = r.replace(/d+/gi,(d<10?"0":"")+d);
            return r; 
        }
//调用方式
        alert((new Date()).format());
        alert((new Date()).format("y-m-d"));
        alert((new Date()).format("y/m/d"));
        alert((new Date()).format("m/d/y"));