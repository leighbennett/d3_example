<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>D3 Graph Examples</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <script src="../3rd-party-js/d3-v5.12.0/d3.js"></script>
        <script src="../3rd-party-js/vue-2.6.10/vue.js"></script> 
    </head>
    <style>
        body {
            background-color: #333333;
            font-family: Arial, Helvetica, sans-serif;
            /*font-size:1.3rem;	*/		
            font-size: calc(18px + (19 - 14) * ((100vw - 300px) / (1600 - 300)));
            color: #ffffff;   
        }
        h2{ color: #d40808; }
        #graphs{
            width:50%; 
            margin:0% 25% 0%;	
        }
        #loader{
            border: 16px solid #a1a112; 
            border-top: 16px solid #f7f71d;   
            border-radius: 50%;
            width: 120px;
            height: 120px;
            animation: spin 2s linear infinite;
            position: fixed;
            top:46%;
            right: 46%;
            z-index: 1000000 !important;
        }
        @keyframes spin{
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @media only screen and (max-width: 768px) {
             #graphs{
                width: 98%; 
                margin:0% 1%;	
             }
        }
    </style>
    <body> 
        <center id="graphs">
            <h2>D3 Graph Examples</h2>
            <span>
                <label>Select time period :</>
                <select v-model="data_file">
                    <option :value="'line_graph_week.json'" selected>Week</option>
                    <option :value="'line_graph_month.json'">Month</option>
                </select>
                <div id="line_graph" class="svg-container"></div>	
            </span>
            <span>			
                <div id="bar_graph"></div>	
            </span>		       
            <div id="loader" v-if="loaderShow"></div>  
        </center>
    </body>
    <script>
        var app = new Vue({
            el: '#graphs',
            data: {
                loaderShow: true,    
                data_file: 'line_graph_week.json'	
            },
            created() {
                app = this;	
                this.lineGraph();	
                this.barGraph();
            },
            watch: {
                data_file: function(val){
                    this.lineGraph();
                }
            },
            methods: {
                lineGraph: function(){
                    app.loaderShow = true;
                    d3.json("dummy_data/"+app.data_file).then(function(data){
                        var margin = {top: 30, right: 20, bottom: 70, left: 75},
                        width = 900 - margin.left - margin.right,
                        height = 390 - margin.top - margin.bottom;
                        var x = d3.scaleBand().range([0, width]).align(0).paddingInner(1.0).paddingOuter(0); 
                        var y = d3.scaleLinear().range([height,0]);
                        var color = d3.scaleOrdinal().range(["#337ab7", "#cc0000", "#ff8c1a"]);
                        var xAxis = d3.axisBottom().scale(x);
                        var yAxis = d3.axisLeft().scale(y).ticks(5).tickPadding(20);
                        var value_line = d3.line()
                            .defined(function(d){return d.day !== "Sun";})
                            .x(function(d){return x(d.date);})
                            .y(function(d){return y(d.price);});
                        var svg = d3.select("#line_graph")
                            .html("")
                            .append("svg")
                            .attr("width", width + margin.left + margin.right)
                            .attr("height", height + margin.top + margin.bottom)
                            .call(app.responsivefy)
                            .append("g")
                            .attr("transform", "translate(" + margin.left + "," + margin.top + ")");
                        x.domain(data.map(function(d){return d.date;}));
                        y.domain([0, d3.max(data, function(d) { return d.price; })]);
                        var dataNest = d3.nest()
                            .key(function(d) {return d.symbol;})
                            .entries(data);
                        var legendSpace = width/dataNest.length;
                        dataNest.forEach(function(d,i){
                        svg.append("path")
                           .attr("class", "line")
                           .style("stroke",function(){return d.color = color(d.key);})
                           .attr("d", value_line(d.values))
                           .attr("fill", "None")
                           .style("stroke-width", "0.125em");
                        svg.append("text")
                           .attr("x", (legendSpace/2)+i*legendSpace) 
                           .attr("y", height + (margin.bottom/2)+ 5)
                           .attr("class", "legend")   
                           .style("fill", function(){return d.color = color(d.key);})
                                .text(d.key);
                           });
                        svg.append("g")
                           .attr("class", "x axis")
                           .attr("transform", "translate(0," + height + ")")
                           .call(xAxis);
                        svg.append("g")
                           .attr("class", "y axis")
                           .call(yAxis);
                        app.loaderShow = false;
                    },function(error){alert("AJAX fetch of line_graph_week.json " + error.responseURL + " failed!");});
                },	
                barGraph: function(){ 
                    app.loaderShow = true;
                    d3.json("dummy_data/bar_graph.json").then(function(data){
                        var margin = {top: 30, right: 20, bottom: 70, left: 75},
                        width = 900 - margin.left - margin.right,
                        height = 390 - margin.top - margin.bottom;
                        var x = d3.scaleBand().rangeRound([0, width]).paddingInner(0.1);
                        var y = d3.scaleLinear().range([height, 0]);
                        var xAxis = d3.axisBottom().scale(x);
                        var yAxis = d3.axisLeft().scale(y).ticks(10).tickPadding(20);
                        var svg = d3.select("#bar_graph")
                            .append("svg")
                            .attr("width", width + margin.left + margin.right)
                            .attr("height", height + margin.top + margin.bottom)
                            .call(app.responsivefy)
                            .append("g")
                            .attr("transform","translate(" + margin.left + "," + margin.top + ")");
                        data.forEach(function(d){
                            d.Zone = d.ZONE;
                            d.Turnover= +d.TURNOVER;
                        });
                        x.domain(data.map(function(d){ return d.Zone; }));
                        y.domain([0, d3.max(data,function(d){ return d.Turnover;})]);
                        svg.append("g")
                           .attr("class", "x axis")
                           .attr("transform", "translate(0," + height + ")")
                           .call(xAxis)
                           .selectAll("text")
                           .style("text-anchor", "end")
                           .attr("dx", "-1em")
                           .attr("dy", "-.75em")
                           .attr("transform", "rotate(-90)" );
                        svg.append("g")
                           .attr("class", "y axis")
                           .call(yAxis)
                           .append("text")
                           .attr("transform", "rotate(-90)")
                           .attr("y", 5)
                           .attr("dy", ".1em")
                           .style("text-anchor", "start");
                        svg.selectAll("bar")
                           .data(data)
                           .enter().append("rect")
                           .attr("class", "bar")
                           .attr("fill", "#cc0000")
                           .on("mouseover", function(d){
                               d3.select(this).style("fill", "#ff8c1a");
                           })                  
                           .on("mouseout", function(d){
                               d3.select(this).style("fill", "#cc0000");
                           })
                           .attr("x", function(d){ return x(d.Zone); })
                           .attr("width", x.bandwidth())
                           .attr("y", function(d){ return y(d.Turnover); })
                           .attr("height", function(d){ return height - y(d.Turnover); });
                        svg.append("text")
                           .attr("dx", "20em")
                           .attr("dy", "-0.7em")
                           .attr("text-anchor", "middle")  
                           .style("font-size", "20px") 
                           .style("fill", "white")
                           .text("Average Turnover Per Zone");
                        app.loaderShow = false;
                    },function(error){alert("AJAX fetch of bar_graph.json " + error.responseURL + " failed!");});
                },
                responsivefy: function(svg){
                    const container = d3.select(svg.node().parentNode),
                    width = parseInt(svg.style('width'), 10),
                    height = parseInt(svg.style('height'), 10),
                    aspect = width / height;
                    svg.attr('viewBox', `0 0 ${width} ${height}`)
                    .attr('preserveAspectRatio', 'xMinYMid')
                    .call(resize);
                    d3.select(window).on('resize.' + container.attr('id'), resize);
                    function resize(){
                        const targetWidth = parseInt(container.style('width'));
                        svg.attr('width', targetWidth);
                        svg.attr('height', Math.round(targetWidth / aspect));
                    }
                }	
            }
        })
    </script>
</html>
