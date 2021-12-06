let restrictions = document.getElementsByClassName('deleteRestriction');

for (let restriction of restrictions) {
    restriction.addEventListener("click", function (e) {
        const xhttp = new XMLHttpRequest();
        const id = e.target.attributes['data-id'].value;
        const url = '/constraints/' + id + '/remove';
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("row-" + id).remove();
            }
        };
        xhttp.open("POST", url, true);
        xhttp.send();
    });
}
