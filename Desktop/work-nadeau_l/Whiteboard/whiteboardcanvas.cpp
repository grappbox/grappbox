#include "whiteboardcanvas.h"

WhiteboardCanvas::WhiteboardCanvas(QWidget *parent) : QWidget(parent)
{
    _Scene = new QGraphicsScene(this);
    _View = new QGraphicsView(this);
    _View->setScene(_Scene);

    _Scene->addLine(0, 0, 100, 100);


}

