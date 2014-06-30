$(document).ready(function(){

    // Section for functions that manipulates the JSON returned from service and turns it into the correct format to be consumed by jqBarGraph
    // I am taking the top 5 most manipulated attributes in our stylesheet and making a bar graph out of it, but can really do
    // anything with the JSON returned from the service

    var graphData = [];
    var graphColor = "black";
    var fourError = "400-error";
    var typeError = "type-error";

    // Count the # of times each attribute is cited in style sheet
    function countAttributes(data){
        countAttributes.cssAttributes = {};
        for(var i in data){
            if(data.hasOwnProperty(i)){
                for(var x in data[i]){
                    if(data[i].hasOwnProperty(x)){
                        // If we already have said attribute saved as a property, increment it,  If not, create the property with value of 1
                        if(countAttributes.cssAttributes[x]){
                            countAttributes.cssAttributes[x] = countAttributes.cssAttributes[x] + 1;
                        }
                        else{
                            countAttributes.cssAttributes[x] = 1;
                        }
                    }
                }
            }
        }
    }

    // Convert the object we created into an array
    function convertToArray(object){
        // Temp array that we use to transport data into the bigger array
        var itemArray = [];
        for(var i in object){
            if(object.hasOwnProperty(i)){
                itemArray.push(object[i]);
                itemArray.push(i);
                itemArray.push(graphColor);
            }
            graphData.push(itemArray);
            itemArray = [];
        }
    }

    // Sort our multi-dimensional array along the # of times they are cited
    function sortArray(){
        graphData.sort(function(a, b){
            return b[0] - a[0];
        });
    }

    // Take the top 5 results
    function getTopResults(){
        graphData = graphData.slice(0, 5);
    }

    function readyGraphData(data){
        countAttributes(data);
        convertToArray(countAttributes.cssAttributes);
        sortArray();
        getTopResults();
    }

    //////////////////////////////////////////////////

    $("#button").on("click", function(){
        // Grab and massage our file data
        var fileData = new FormData();
        var uploadedData = $("#file").get(0);
        fileData.append("file", uploadedData.files[0]);
        $.ajax({
            url: "app.php",
            data: fileData,
            type: "POST",
            // We're sending a file so contentType and processData need to be set to false
            contentType: false,
            processData: false,
            success: function(data){
                if(data === fourError || data === typeError){
                    $("#file-error").show();
                }
                else{
                    var parsedData = $.parseJSON(data);
                }
                // Check if response is a success. If so, run DOM manipulation
                if(parsedData.success === true){
                    $("#file-error").hide();
                    readyGraphData(parsedData.data);
                    // Plot our graph
                    $("#graph").jqbargraph({
                         data: graphData
                    });
                    // Update and show links for CSS and JSON files
                    $("#css-link").show().attr({
                        href: "uploads/" + parsedData.css_file
                    });
                    $("#json-link").show().attr({
                        href: "processed/" +  parsedData.json_file
                    });
                }
            }
        });
        return false;
    });

});
