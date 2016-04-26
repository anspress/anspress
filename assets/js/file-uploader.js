(function($){

    function FileUploader(ID){
        this.form = document.getElementById(ID);
        this.uploadTemplate = document.getElementById('uploadFormTemplate');
        this.initialise();
    }
    
    FileUploader.PROGRESS_ANIMATION_DELAY = 3000;
    
    FileUploader.prototype.initialise = function(){
        this.constructTemplate();
        
        this.inputsWrapper = this.form.querySelector('.inputs');
        this.uploadCaption = this.form.querySelector('.uploadCaption');
        this.uploadQueue = this.form.querySelector('.uploadQueue');
        
        this.uploadCaption.addEventListener('click', this.handleAddNewFile.bind(this), false);
        this.uploadCaption.addEventListener('dragover', this.handleDragOverEnter.bind(this), false);
        this.uploadCaption.addEventListener('dragenter', this.handleDragOverEnter.bind(this), false);
        this.uploadCaption.addEventListener('drop', this.handleDragDropNewFile.bind(this), false);
    };
    
    FileUploader.prototype.constructTemplate = function(){
        this.form.innerHTML += this.uploadTemplate.innerHTML
    };

    FileUploader.prototype.handleAddedFile = function(e){
        var files = e.dataTransfer ? e.dataTransfer.files : e.target.files;
        console.log(files);
        if (files) {
            var queueItem;
            for (var i = 0, numOfFiles = files.length; i < numOfFiles; i++) {
                queueItem = this.addQueueItem(files[i]);
                this.validateFile(queueItem, files[i]);
            }
        }
    };
    
    FileUploader.prototype.handleNewFileSelection = function(){
        // Generate a new file input to use only if the last existing file input is not empty
        if (!this.fileInput || this.fileInput[this.fileInput.length - 1].files.length >= 1) {
            // Add new file input
            var input = document.createElement('input');
            input.setAttribute('class', 'fileInput');
            input.setAttribute('type', 'file');
            input.setAttribute('multiple', 'multiple');
            
            this.inputsWrapper.appendChild(input);
            input.addEventListener('change', this.handleAddedFile.bind(this), false);
        }
    };
    
    FileUploader.prototype.handleAddNewFile = function(e){
        this.handleNewFileSelection();
        this.fileInput = this.inputsWrapper.querySelectorAll('.fileInput');
        this.fileInput[this.fileInput.length - 1].click();
        e.preventDefault();
    };
    
    FileUploader.prototype.handleDragDropNewFile = function(e){
        e.stopPropagation();
        e.preventDefault();
        this.handleAddedFile(e);
    };
    
    FileUploader.prototype.handleDragOverEnter = function(e) {
        e.stopPropagation();
        e.preventDefault();
    };
    
    FileUploader.prototype.addQueueItem = function(file){
        // Add queue item wrapper
        var queueItem = document.createElement('div');
        queueItem.className += ' queueItem';
        
        // Generate File upload text
        var newUpload = document.createElement('p'),
            fileSize = Math.round(file.size / 1000),
            sizeType = (fileSize > 1000) ? 'mb' : 'kb',
            progressBar = this.addProgressBar();
        newUpload.innerText = "Uploading "+file.name + " | "+fileSize+sizeType;
        
        queueItem.appendChild(newUpload);
        queueItem.appendChild(progressBar);
        
        this.uploadQueue.appendChild(queueItem);
        return queueItem;
    };
    
    FileUploader.prototype.addProgressBar = function(){
        var progressBarWrapper = document.createElement('div'),
            progressBar = document.createElement('div');
        
        progressBarWrapper.setAttribute('class', 'progressBar');
        progressBar.setAttribute('class', 'bar');
        progressBarWrapper.appendChild(progressBar);
        
        this.uploadQueue.appendChild(progressBarWrapper);
        return progressBarWrapper;
    };
    
    FileUploader.prototype.validateFile = function(queueItem, file){
        if (file.type.match(/image/) || file.type === 'application/pdf') {
            this.simulateUpload(queueItem, file);
        } else {
            var errorTxt = document.createElement('p');
            errorTxt.className += 'error-txt';
            errorTxt.innerText = file.name+' is invalid file type.';
            queueItem.innerHTML = '';
            queueItem.appendChild(errorTxt);
        }
    };
    
    FileUploader.prototype.simulateUpload = function(queueItem, file){
        if (queueItem && file) {
            queueItem.className += ' simulatedProgress';
            setTimeout(function(){
                queueItem.className += ' resolved';
                var txt = queueItem.getElementsByTagName('p');
                txt[0].innerText = file.name+' successfully uploaded.';
                txt[0].className += 'success-txt';
            }, FileUploader.PROGRESS_ANIMATION_DELAY);
        }
    };
    
    var fileIDUploader = new FileUploader('uploadIDForm'),
        fileAddressUploader = new FileUploader('uploadAddressForm');
}());