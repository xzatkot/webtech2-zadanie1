function search() {
    let url = '/z1/index.php?page=0&search=' + document.getElementById('searchField').value;
    window.location.assign(url);
}

function sort(num) {
    let url = window.location.href;
    if (num === 1) {
        if (!url.includes("sort"))
        url += '&sort=surname';
    } else if (num === 2) {
        url += '&sort=year';
    } else if (num === 3) {
        url += '&sort=type';
    }
    window.location.assign(url);
}

function next(count, page, max) {
    if (count - ((page+1) * max) > 0) {
        if (!window.location.href.includes("?")) {
            let new_url = window.location.href + "?page=1";
            window.location.assign(new_url);
            return;
        }
        let url = window.location.href;
        let pageNum = Number(url.split("?")[1].split("&")[0].split("=")[1]);
        let nextPage = pageNum + 1;
        let new_url = url.replace("page="+pageNum, "page="+nextPage);
        window.location.assign(new_url);
    }
}

function prev() {
    let url = window.location.href;
    let pageNum = Number(url.split("?")[1].split("&")[0].split("=")[1]);
    let nextPage = pageNum - 1;
    if (nextPage >= 0) {
        let new_url = url.replace("page="+pageNum, "page="+nextPage);
        window.location.assign(new_url);
    }
}