/**
 * Nodo
 * NodoGraph (NG) represents a nodo d3 graphics object.
 * d3.js JavaScript library (http://d3js.org/) is used for this.
 * D3 allows you to bind arbitrary data to a Document Object
 * Model (DOM), and then apply data-driven transformations to
 * the document.
 * For more information on data driven documents, @link http://d3js.org
 *
 * @package   nodo
 * @author    m7red (http://www.m7red.info)
 * @license   http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @copyright Copyright 2015, m7red
 */

"use strict";
var NG;
NG = {
  version: '2015.07.09',            // current object version.
  debug: !1,                        // debug mode for testing.
  isError: false,                   // error code as an internal abort criteria.
  w: 960,                           // default graphics width.
  h: 450,                           // default graphics height.
  container: '#graph_container',    // graphics div container.
  linkDistance: 80,                 // distances between nodes.
  linkedByIndex: {},                // checking for nodes in neighbourhood.
  charge: -500,                     // sets the charge strength to the specified value.
  maxNodeSize: 9996,                //
  chartElement: "#chart",           //
  force: [],                        // force layout object
  svg: [],                          // SVG container to hold the visualization.
  links: [],                        // links container
  nodes: [],                        // nodes container
  data: [],                         // data container
  link: '',                         // SVG line
  strokeWidthLink: 1.5,             // link width
  strokeColorLink: '#666',          // link color
  strokeWidthNeighbourLink: 3.5,    // Width to related links.
  strokeColorNeighbourLink: '#fff',           // Color to related links.
  strokeColorNeighbourLinkSingle: '#FF9900',  // Color in case of single view, e.g. post.
  nodeTypes: [],                    // when different types of nodes are used.
  nodeTextLabelColor: 'white',      // Textlabel color, normally depends on background.
  graphType: 1,                     //
  filterType: '',                   // filter type: e.g. post, category, region.
  filterValues: [],                 // filter values: e.g. post id, category title.
  filterRelatedOnly: !1,            // show related entries only in graphics.
  filterGrayShadeColor: '#323232',  // for text, labels, edges, strokes, etc.
  node: function() {                // Selects the specified nodes.
    return d3.select("g#nodes").selectAll("g.node");
  },
  dataURL: document.location.href + ".js",  //
  fill: d3.scale.category10(),  // Constructs an ordinal scale with a range of ten colors.
  zoomListener: null,           // to SVG's zoom behavior.
  zoomInitTranslateVectorX: 0,  // Specifies the init zoom translation vector for X.
  zoomInitTranslateVectorY: 0,  // Specifies the init zoom translation vector for Y.
  zoomInitScale: 1,             // Specifies the current zoom scale.
  // Graph goes up here...
  run: function() {
    NG.isError = false;
    NG.debug && console.log('NodoGraph ' + NG.version + ' running...');
    NG.loadData();
  },
  loadData: function() {
    d3.json(NG.dataURL, function(error, b) {
      if (error) {
        NG.isError = true;
        console.error(error);
        return;
      }
      // 1st of all an intermediate step for data cleansing.
      NG.cleansingData(b);
      // Apply laundered data as database for further proceeding.
      NG.links = b.links;
      NG.nodes = b.nodes_attr;
      NG.data = b;
      // Make sure whether data is present.
      NG.checkIfData();
      if (!NG.hasData) { return; }
      // Check whether a filter exists and if so set filter criteria.
      if (NG.filterType && (/^post|category|region|process$/).test(NG.filterType) &&
          NG.filterValues && NG.filterValues.length > 0) {
        NG.setFilter();
        NG.checkIfData(); // Check data presence again.
        if (!NG.hasData) { return; }
      }
      // Launch data visualization.
      NG.drawGraph();

//       NG.isError = true;
//       if (NG.isError) { return; }
    });
  },
  // Check if data is still present.
  checkIfData: function() {
    NG.hasData = Boolean(NG.links.length);
    if (!NG.hasData) { NG.debug && console.log('No data available.'); }
  },
  cleansingData: function(b) {
    // Clean up links...
    // Check data for link relations without existing nodes.
    // Those ghost entries will cause errors during the graphics
    // build process afterwards and therefore we remove it here.
    NG.debug && console.log('Cleanup links...');
    NG.debug && console.log('Total links: '+ b.links.length);
    NG.debug && console.log('Total Nodes: '+ Object.getOwnPropertyNames(b.nodes_attr).length);
    var links_copy = b.links.concat();
    var i;
    for (i=0; i<links_copy.length; i++) {
      NG.debug && console.log('checking target ' + links_copy[i]['target']);
      if (!b.nodes_attr.hasOwnProperty(links_copy[i]['target'])) {
        NG.debug && console.debug('target '+links_copy[i]['target']+' not found -> removed');
        delete b.links[i];
      } else {
        NG.debug && console.log('checking source ' + links_copy[i]['source']);
        if (!b.nodes_attr.hasOwnProperty(links_copy[i]['source'])) {
          NG.debug && console.debug('source '+links_copy[i]['source']+' not found -> removed');
          delete b.links[i];
        }
      }
    }
    // Clean up links data from deleted undefined gaps.
    i = 0;
    while (i < b.links.length) {
      (typeof b.links[i] === 'undefined') ? b.links.splice(i, 1) : i++;
    }
    NG.debug && console.log('Total links: '+ b.links.length);
    NG.debug && console.log('Total nodes: '+ Object.getOwnPropertyNames(b.nodes_attr).length);
    NG.debug && console.log('Cleanup links finished.');

    // Clean up also nodes...
    NG.debug && console.log('Cleanup nodes...');
    NG.debug && console.log('Total links: '+ b.links.length);
    NG.debug && console.log('Total nodes: '+ Object.getOwnPropertyNames(b.nodes_attr).length);
    var nodes_copy = b.nodes_attr;

    var found;
    for (var key in nodes_copy) {
      NG.debug && console.log('checking node ' + nodes_copy[key].id);
      found = false;
      for (i=0; i<b.links.length; i++) {
        if (b.links[i].hasOwnProperty('source')) {
          if (b.links[i]['source'] == nodes_copy[key].id) {
            found = true;
            break;
          }
        }
      }
      if (found === false) {
        for (i=0; i<b.links.length; i++) {
          if (b.links[i].hasOwnProperty('target')) {
            if (b.links[i]['target'] == nodes_copy[key].id) {
              found = true;
              break;
            }
          }
        }
      }

      if (found === false) {
        NG.debug && console.debug('node '+ b.nodes_attr[key].id +' not found: -> removed');
        // Delete node.
        delete b.nodes_attr[key];
      }
    }

    NG.debug && console.log('Total links: '+ b.links.length);
    NG.debug && console.log('Total nodes: '+ Object.getOwnPropertyNames(b.nodes_attr).length);
    NG.debug && console.log('Cleanup nodes finished.');
  },
  drawGraph: function() {
    NG.debug && console.log('drawGraph...');

    // Set data model.
    var nodes = {};
    var links = {};
    links = NG.links;
    links.forEach(function (link) {
      link.source = nodes[link.source] || (nodes[link.source] = {id: link.source});
      link.target = nodes[link.target] || (nodes[link.target] = {id: link.target});
    });

    // Running the animation.
    var tick = function(e) {
      link
        .attr("x1", function(d) { return d.source.x; })
        .attr("y1", function(d) { return d.source.y; })
        .attr("x2", function(d) { return d.target.x; })
        .attr("y2", function(d) { return d.target.y; });

      node
        .attr("transform", function(d) {
          // Center selected node (Experimental!).
          if (NG.filterType === 'post' && !NG.filterRelatedOnly) {
            if (d.id === NG.filterValues[0] && !d.fixed) {
              NG.debug && console.log("centering node... ");
              d.x = NG.w / 2;
              d.y = NG.h / 2;
            }
          }
          return "translate(" + d.x + "," + d.y + ")";
        });

      node
        .attr("cx", function(d) { return d.x; })
        .attr("cy", function(d) { return d.y; });
    };

    // Highlighting the neighboring nodes if single post view.
    // Note: At present we use only the first element of NG.filterValues in this function!
    //       This is because it's only for a single post view.
    var highlightNeighbors = function(d) {
      // Get index for selected id.
      var node_idx = false;
      var data = d3.selectAll('.node').data();
      var i;
      for(i=0;i<data.length;i++) {
        if (data[i].id === NG.filterValues[0]) {
          node_idx = data[i].index;
          break;
        }
      }

      // Highlight node circle.
      if (node_idx) {
        d3.select(node[0][node_idx]).select("circle").transition()
          .attr("r", NG.nodes[NG.filterValues[0]].size * 2)
          .duration(150)
          .style("fill", '#e3e3e3');
      }

      // Highlight edges.
      link.style('stroke-width', function(l) {
        if (NG.filterValues[0] === l.source.id || NG.filterValues[0] === l.target.id) {
          return NG.strokeWidthNeighbourLink;
        } else {
          return NG.strokeWidthLink;
        }
      });

      link.style('stroke', function(l) {
        if (NG.filterValues[0] === l.source.id || NG.filterValues[0] === l.target.id) {
          return NG.strokeColorNeighbourLinkSingle;
        } else {
          return NG.strokeColorLink;
        }
      });
    };

    // After click on a node jump to it's post.
    var nodeOnClicked = function(d) {
      return location.href = NG.nodes[d.id].url;
    };

    // Shows relations of selected node.
    var nodeOnMouseover = function(d, i) {
      d3.select(this).select("circle").transition()
        .attr("r", NG.nodes[d.id].size * 2)
        .style('stroke', NG.nodes[d.id].border_color)
        .style('stroke-width', 5)
        .style("cursor", "pointer")
        .duration(150)
        .style("fill", '#e3e3e3');
        tip.show(d, i);

      link.style('stroke-width', function(l) {
        if (d === l.source || d === l.target) {
          return NG.strokeWidthNeighbourLink;
        } else if (NG.filterType == 'post' && !NG.filterRelatedOnly &&
                (NG.filterValues[0] === l.source.id ||
                 NG.filterValues[0] === l.target.id)) {
          return NG.strokeWidthNeighbourLink;
        } else {
          return NG.strokeWidthLink;
        }
      });

      link.style('stroke', function(l) {
        if (d === l.source || d === l.target) {
          return NG.strokeColorNeighbourLink;
        } else if (NG.filterType == 'post' && !NG.filterRelatedOnly &&
                (NG.filterValues[0] === l.source.id ||
                 NG.filterValues[0] === l.target.id)) {
          return NG.strokeColorNeighbourLinkSingle;
        } else {
          return NG.strokeColorLink;
        }
      });

      // Highlight node label also in case of filtered status.
      if ((NG.filterType && (/^category|region|process$/).test(NG.filterType)) &&
          !NG.filterRelatedOnly &&
          NG.filterValues.indexOf((NG.nodes[d.id].cluster).toString()) === -1) { // not in array
        d3.select(this).select('text').select('tspan')
          .style('fill', NG.nodeTextLabelColor);
      }

      // Display tooltip in the upper left graphics container corner.
      // Browsers zoom factor is also considered with this simply method.
      var elem_graph_container = document.getElementById('graph_container');
      NG.debug && console.log(
        "elem_graph_container.offsetLeft: " + elem_graph_container.offsetLeft,
        "elem_graph_container.offsetTop: " + elem_graph_container.offsetTop
      );
      tip
        .style('top', elem_graph_container.offsetTop + 'px')
        .style('left', elem_graph_container.offsetLeft + 'px');
    };

    // Return to standard view.
    var nodeOnMouseout = function(d, i) {
      d3.select(this).select("circle").transition()
      .attr("r", function(d) { return NG.nodes[d.id].size; })
      .duration(150)
      .style("fill", function(d) { return NG.nodes[d.id].color; })
      .style("stroke", function(d) { return NG.nodes[d.id].color; });
      tip.hide();

      link.style('stroke-width', NG.strokeWidthLink);
      link.style('stroke', NG.strokeColorLink);

      // Restore neighbor highlighting if single post view.
      if (NG.filterType === 'post' && !NG.filterRelatedOnly) { highlightNeighbors(); }

      // Restore node label color in case of filtered status.
      if ((NG.filterType && (/^category|region|process$/).test(NG.filterType)) &&
          !NG.filterRelatedOnly &&
          NG.filterValues.indexOf((NG.nodes[d.id].cluster).toString()) === -1) { // not in array
        d3.select(this).select('text').select('tspan')
          .style('fill', NG.filterGrayShadeColor);
      }
    };

    // Set force layout
    // More details about this function: https://github.com/mbostock/d3/wiki/Force-Layout
    NG.force = d3.layout.force()      // constructs a new force-directed layout
      .nodes(d3.values(nodes))        // set 'nodes' to nodes
      .links(links)                   // and 'links' to links
      .size([NG.w, NG.h])             // sets the available layout size to our values
      .linkDistance(NG.linkDistance)  // sets the target distance between linked nodes
      .charge(NG.charge)              // sets the force between nodes
      .on('tick', tick)               // runs the animation of the force layout one 'step'
      .start();                       // starts the simulation

    // Set zoom behavior. At first we set zoom width and height to center graphics.
    NG.zoomInitTranslateVectorX = (NG.w - (NG.zoomInitScale * NG.w)) / 2;
    NG.zoomInitTranslateVectorY = (NG.h - (NG.zoomInitScale * NG.h)) / 2;
    NG.zoomListener = d3.behavior.zoom()
      .translate([NG.zoomInitTranslateVectorX,
          NG.zoomInitTranslateVectorY]) // Specifies the current zoom translation vector.
      .scale(NG.zoomInitScale) // Specifies the current zoom scale which defaults to 1.
      .scaleExtent([0.5, 8]) // Specifies the two min and max scaling ratio.
      .on("zoom", zoomHandler); // Defines the zoom event by calling zoomHandler function.

    // Set up graphics container div.
    NG.svg = d3.select(NG.container).append("svg")
      .attr("width", NG.w)
      .attr("height", NG.h)
      .attr("pointer-events", "all")
      .append('svg:g')
      .call(NG.zoomListener);

    NG.svg.append('svg:rect')
      .attr('width', NG.w)
      .attr('height', NG.h)
      .attr('fill', 'transparent');

    // Init zoom.
    // Activate the zoom event and pass in the transition with a duration of 500ms.
    NG.zoomListener.event(NG.svg.transition().duration(500));

    // Init links.
    var link = NG.svg.selectAll(".link")
      .data(NG.force.links())
      .enter().append("line")
      .attr("class", "link");

    // Init nodes.
    var node = NG.svg.selectAll(".node")
      .data(NG.force.nodes())
      .enter().append("g")
      .attr("class", "node")
      .on('click', nodeOnClicked)
      .on('mouseover', nodeOnMouseover)
      .on('mouseout', nodeOnMouseout)
      .call(NG.force.drag);  // Bind a behavior to nodes to allow interactive dragging.

    // Append circles to nodes.
    node.append("circle")
      .style("fill", function(d) { return NG.nodes[d.id].color; })
      .style("stroke", function(d) { return NG.nodes[d.id].color; })
      .attr("r", function(d) { return NG.nodes[d.id].size; });

    // Node (circle) labels.
    var text = node.append("text")
      .attr("x", 12)
      .attr("dy", ".35em")
      .attr('class', 'node-textlabel');

    // Depending on filter status, set a specific label text color for nodes.
    if ((NG.filterType && (/^category|region|process$/).test(NG.filterType)) &&
      !NG.filterRelatedOnly) {
      if (NG.filterType == 'category') {
        text.append("svg:tspan")
          .style("fill", function(d) { // If value is in filter set text label color.
            return (NG.filterValues.indexOf((NG.nodes[d.id].cluster).toString()) !== -1) ?
              NG.nodeTextLabelColor : NG.filterGrayShadeColor;
          })
          .text(function(d) { return NG.nodes[d.id].label; });
      }
      if (NG.filterType == 'region') {
        text.append("svg:tspan")
          .style("fill", function(d) { // If value is in filter set text label color.
            return (NG.filterValues.indexOf((d.id).toString()) !== -1) ?
              NG.nodeTextLabelColor : NG.filterGrayShadeColor;
          })
          .text(function(d) { return NG.nodes[d.id].label; });
      }
      if (NG.filterType == 'process') {
        text.append("svg:tspan")
          .style("fill", function(d) { // If value is in filter set text label color.
            return (NG.filterValues.indexOf((d.id).toString()) !== -1) ?
              NG.nodeTextLabelColor : NG.filterGrayShadeColor;
          })
          .text(function(d) { return NG.nodes[d.id].label; });
      }
    }
    else {
      text.append("svg:tspan")
        .style("fill", NG.nodeTextLabelColor)
        .text(function(d) { return NG.nodes[d.id].label; });
    }

    // Set tooltip - see also separate css styles for this.
    var tip = d3.tip()
      .attr('class', 'd3-tip') // set style, defined in style.css
      // Offset tooltip relative to its calculated position.
      // Offset is computed from [top, left].
      .offset([0, 0])
      .html(function(d) {
        return NG.nodes[d.id].excerpt;
      });
    // Invoke the tip in the context of visualization.
    NG.svg.call(tip);

    // Create a matrix which allows constant-time lookup to test
    // whether a and b are neighbors.
    var linkedByIndex = {};
    links.forEach(function(d) {
      linkedByIndex[d.source.index + "," + d.target.index] = 1;
    });

    // Zoom handler for controlling zoom behavior.
    function zoomHandler() {
//     console.log("zoomHandler()", d3.event.translate, d3.event.scale);
      NG.svg.attr("transform",
        "translate(" + d3.event.translate + ")"
        + " scale(" + d3.event.scale + ")");
    }

    // Highlight neighbors if single post view.
    if (NG.filterType === 'post' && !NG.filterRelatedOnly) { highlightNeighbors(); }
  },
  setFilter: function() {
    //
    // Filter methods:
    // Generally we could say data structure is post based with categories as clusters.
    // Filters are based on a particular type information and it's values as an array.
    // Regarding to posts there is only one value in associated array - the post id.
    // In case of categories, there is an information called «cluster» for each post
    // which is also a single value. This value is different from the post id!
    // For regions one or more categories could be filtered. It is an indirect
    // transformation from one region to one or many categories. Thus, base for this is
    // also post data, which means that array data is post ids.
    //
    NG.debug && console.log('setFilter "' +NG.filterType+ '"...');
    // If all post entries should be shown we abort here.
    // There is no need for filter efforts.
    // This is the default behavior for posts.
    if (NG.filterType == 'post' && !NG.filterRelatedOnly) { return; }

    switch (NG.filterType) {
      case 'post':
        // First we make a deep copy of links array into iterator array.
        var json_s = JSON.stringify(NG.links);
        var all_links = JSON.parse(json_s);
        var i;
        // Delete all not related entries in links.
        for (i in all_links) {
          if (all_links[i].source != NG.filterValues[0] &&
              all_links[i].target != NG.filterValues[0]) {
              delete NG.links[i];
          }
        }
        // Now clean up links data from deleted undefined gaps.
        i = 0;
        while (i < NG.links.length) {
          (typeof NG.links[i] === 'undefined') ? NG.links.splice(i, 1) : i++;
        }
      break;

      case 'category':
        // Set node colors to all none selected categories to gray.
        for (var i in NG.nodes) {
          if (NG.filterValues.indexOf((NG.nodes[i].cluster).toString()) === -1) {// not in array
            NG.nodes[i].color = NG.filterGrayShadeColor;
          }
        }
      break;

      case 'region':
        // Set node colors to all none selected categories (as well as above!) to gray.
        // This is an indirect transformation from one region to one or many categories.
        // Thus, base for this is post id data.
        for (var i in NG.nodes) {
          if (NG.filterValues.indexOf((NG.nodes[i].id).toString()) === -1) {// not in array
            NG.nodes[i].color = NG.filterGrayShadeColor;
          }
        }
      break;

      case 'process':
        // Set node colors to all none selected categories (as well as above!) to gray.
        // This is an indirect transformation from one process to one or many categories.
        // Thus, base for this is post id data.
        for (var i in NG.nodes) {
          if (NG.filterValues.indexOf((NG.nodes[i].id).toString()) === -1) {// not in array
            NG.nodes[i].color = NG.filterGrayShadeColor;
          }
        }
      break;
    }
    NG.debug && console.log('setFilter done.');
  },
  neighboring: function(a, b) {
//     return NG.linkedByIndex[b.index + "," + a.index]
    return NG.linkedByIndex[a.index + "," + b.index] ||
          NG.linkedByIndex[b.index + "," + a.index] || a.index == b.index;
  }
};
