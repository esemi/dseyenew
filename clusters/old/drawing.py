# -*- coding: utf-8 -*-

__author__="esemi"

from PIL import Image, ImageDraw, ImageFont

def drawdendogram(clust, labels, filename='clusters.jpg'):
    # height and width
    h = getheight(clust)*20
    w = 6000
    depth = getdepth(clust);

    # width is fixed, so scale distances accordingly
    scaling = float(w-150) / depth;

    # create a new image ith a white background
    img = Image.new('RGB', (w,h), (255, 255, 255))
    draw = ImageDraw.Draw(img)

    draw.line((0, h/2, 10, h/2), fill=(255,0,0))

    # draw the first node
    drawnode(draw, clust, 10, (h/2), scaling, labels)
    img.save('data/'+filename, 'JPEG')




def drawnode(draw, clust, x, y, scaling, labels):
    fontPath = "components/FreeMono.ttf";
    font = ImageFont.truetype ( fontPath, 12 );
    
    if clust.id < 0:
        h1 = getheight(clust.left) * 20
        h2 = getheight(clust.right) * 20
        top = y - (h1 + h2) / 2
        bottom = y + (h1 + h2) / 2

        # line length
        ll = clust.distance * scaling

        draw.text((x-50, y-12), '%.4f' % clust.distance, fill='black', font=font )

        # vertical line from this cluster to children
        draw.line((x, top+h1/2, x, bottom-h2/2), fill=(255,0,0))

        # horizontal line to left item
        draw.line((x, top+h1/2, x+ll, top+h1/2), fill=(255,0,0))

        # horizontal line to right item
        draw.line((x, bottom-h2/2, x+ll, bottom-h2/2), fill=(255,0,0))

        # call the function recursively to draw the left and right nodes
        drawnode(draw, clust.left, x+ll, top+h1/2, scaling, labels)
        drawnode(draw, clust.right, x+ll, bottom-h2/2, scaling, labels)
    else:
        # if this is an endpoint, draw the item label
        draw.text((x+5, y-7), unicode(labels[clust.id], 'utf-8'), fill='black', font=font )

def getheight(clust):
    # is this an endpoint? end recursion if so and height is just 1
    if clust.left == None and clust.right == None:
        return 1

    # otherwise the height is equivalent to the height of the 2 branches added
    # together
    return getheight(clust.left) + getheight(clust.right)


# calculate the depth of a cluster node
def getdepth(clust):
    # the distance of an endpoint is 0.0
    if clust.left == None and clust.right == None:
        return 0

    # the distance of a branch is the greater of its two sides plus its own
    # distance
    return max(getdepth(clust.left), getdepth(clust.right)) + clust.distance



