function openInNewTab(url) {

    var win = window.open(url, '_blank');
    
    //Browser has allowed it to be opened
    if (win) {
        win.focus();
    } else {
        alert('Please allow popups for this website');
    }
    
}