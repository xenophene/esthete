#!/usr/bin/python
import json
import sys
from operator import itemgetter
from commands import getoutput
def create_svg_graph(G, actorlist, T, graphfilename, svgfile):
    graphfile = ''; # the graph file on which dot will be run
    graphfile += 'digraph G {\n';
    graphfile += 'node [shape=plaintext, fontsize=16]\n';
    keys = G.keys();
    timelabel = ' Day';
    # to give a time periodic structure to the graph, we add these mystic
    # lambdas which are equal to the number of time periods in question
    # add as many datamarkers as the time slabs
    dates = map(itemgetter(0), keys);
    dates = dict(map(lambda i:(i,1),dates)).keys();
    # print dates;
    graphfile += timelabel + str(1);
    for i in range(len(dates)):
        graphfile += ' -> ' + timelabel + str(dates[i] + 1);
    graphfile += ' -> ' + timelabel + str(dates[-1] + 1) + ';';
    graphfile += 'node [shape=box];\n';
    # now we can start adding the nodes of G, labelled by the headlines
    i = 1;
    nodeid = {}; # the mapping of doc id (t,d) to node id i
    for (t,d) in keys:
        G[(t,d)] = dict(map(lambda i:(i,1),G[(t,d)])).keys();
        actors = actorlist[int(t)][int(d)];
        actorlabel = '';
        for actor in actors:
            actorlabel += actor + '\\n';
        s = str(i) + ' [label="' + actorlabel + '\\n';
        # s = str(i) + ' [label="';
        # along with node label, write the transformations that happened
        # sorted(T[(t,d)], key=itemgetter(3), reverse=True);
        #for trans in T[(t,d)]:
        #    if (trans[0] == 'MERGE' or trans[0] == 'SPLIT'):
        #        s += trans[0] + '(' + trans[1];
        #        s += ', ' + trans[2];
        #        s += ') ' + str(trans[3]) + '\\n';
        #    elif (trans[0] == 'CREATE' or trans[0] == 'CONTINUE' or \
        #                                        trans[0] == 'CEASE'):
        #        s += trans[0] + '(' + trans[1] + ') ' + str(trans[3]) + '\\n';
        graphfile += s  +  '"];' + '\n';
        graphfile += '{rank=same;' + timelabel + str(t) + ';' + str(i) + ';}\n';
        nodeid[(t,d)] = i;
        i += 1;
    nextnodeid = i; # the next available node id for boundary nodes that may
    # have to be created later  
    # now nodes have been set. edges are being set up
    # iterate over the graph G node (t,d), for neighbours with tN < t
    # create an edge tN->t and viceversa
    for (t,d) in keys:
        i = nodeid[(t,d)];
        nbrs = G[(t,d)];
        for (tn, dn) in nbrs:
            # shouldnt give an error as long as node tn,dn is defined
            if (tn,dn) in nodeid.keys():
                j = nodeid[(tn,dn)];
            else:
                # create the new node giving it index nextnodeid
                # draw edges
                j = nextnodeid;
                nextnodeid += 1;
                actors = actorlist[int(t)][int(d)];
                nbractors = actorlist[int(tn)][int(dn)];
                nbractorlabel = '';
                for actor in nbractors:
                    nbractorlabel += actor + '\\n';
                nbractorlabel = nbractorlabel;
                graphfile += str(j) + ' [label="' + nbractorlabel + '"];\n';
                graphfile += '{rank=same;' + timelabel + str(tn) + ';' + str(j) + ';}\n';
                nodeid[(tn,dn)] = j;
                G[(tn,dn)] = [];
            # before drawing the edge we need to figure out the common
            # edge labels as the common of actors and actorlist[tn][dn]
            actors = actorlist[int(t)][int(d)];
            nbractors = actorlist[int(tn)][int(dn)];
            edgelabel = '';
            for actor in actors:
                if actor in nbractors:
                    edgelabel += actor + '\\n';
            if tn < t:
                graphfile += str(j) + ' -> ' + str(i) + ' [label="';
                graphfile += edgelabel + '"];\n';
            elif (t,d) not in G[(tn,dn)]:
                graphfile += str(i) + ' -> ' + str(j) + ' [label="';
                graphfile += edgelabel + '"];\n';
    graphfile += '}'; # the final nail!
    outfile = open('/home/xenoph/' + graphfilename, 'wb');
    outfile.write(graphfile);
    outfile.close();
    getoutput('dot -Tsvg ' + '/home/xenoph/' + graphfilename + ' -o ' + '/home/xenoph/' + svgfile);
    infile = open('/home/xenoph/' + svgfile, 'r')
    svgcode = infile.read()
    infile.close()
    print svgcode
    return (graphfile, nodeid);

if __name__ == '__main__':
  sys.stderr = sys.stdout
  infile = open('/home/xenoph/nodes.json', 'r')
  lines = infile.read()
  infile.close()
  lines = json.loads(lines)
  nodes = lines
  oNodes = lines # corresponds to the original order as intended by php
  # we look at nodes, the entry (t,d) at index i in nodes, is actually the
  # node where the transformations at P[i] happened
  # read in the transformation list and the actor list
  infile = open('/home/xenoph/transformation.json', 'r')
  lines = infile.read()
  infile.close()
  P = json.loads(lines)
  T = {}
  for i in range(len(P)):
    node = (int(oNodes[i].split(',')[0]), int(oNodes[i].split(',')[1]))
    T[node] = P[i]
  G = {}
  for node in nodes:
    G[(int(node.split(',')[0]), int(node.split(',')[1]))] = []
  infile = open('/home/xenoph/neighbours.json', 'r')
  lines = infile.read()
  infile.close()
  lines = json.loads(lines)
  for i in range(len(lines)):
    nbrs = lines[i]
    node = (int(nodes[i].split(',')[0]), int(nodes[i].split(',')[1]))
    for nbr in nbrs:
      x = (int(nbr[0]), int(nbr[1]))
      G[node].append(x)
  removeKey = [key for key in G.keys() if G[key] == []]
  for k in removeKey:
    del G[k]
    del T[k]
  infile = open('/home/xenoph/Actorset.json', 'r')
  lines = infile.read()
  infile.close()
  A = json.loads(lines)
  # remove the nodes which dont contain both of the actors as required by PHP
  infile = open('/home/xenoph/actors.json', 'r')
  lines = infile.read()
  infile.close()
  actors = json.loads(lines)
  #print actors
  # actors is an array of 2 elements, the 2 actors who SHOULD be there
  # iterate over the keys of the graph, for each go to actorlist[t][d], and if
  # either actor is not in the list, remove this node
  removeKey = []
  for (t,d) in G.keys():
    if actors[0] in A[t][d] and actors[1] in A[t][d]: # alrighty, pass!
      #print A[t][d]
      continue
    removeKey.append((t,d))
  for k in removeKey:
    del G[k]
    del T[k]
  nodes = G.keys()
  create_svg_graph(G, A, T, 'test_dot', 'test_svg.html')
