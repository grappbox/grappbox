#include "whiteboardcanva.h"
#include <QDebug>

whiteboardcanva::whiteboardcanva(QWidget *parent) : QGraphicsScene(parent)
{
    _CurrentType = GraphicsType::CIRCLE;
    _CurrentLine = NULL;
}

void whiteboardcanva::mousePressEvent(QGraphicsSceneMouseEvent *event)
{
    QPointF mousePos = event->scenePos();
    switch (_CurrentType)
    {
        case GraphicsType::LINE:
        _CurrentLine = addLine(mousePos.x(), mousePos.y(), mousePos.x(), mousePos.y());
        break;
        case GraphicsType::RECT:
        drawSquare(mousePos.x(), mousePos.y(), 100);
        break;
        case GraphicsType::CIRCLE:
        drawCircle(mousePos.x(), mousePos.y(), 100);
        break;
        case GraphicsType::ERASE:
        break;
    }
}

void whiteboardcanva::mouseMoveEvent(QGraphicsSceneMouseEvent *event)
{
    if (_CurrentLine)
    {
        _CurrentLine->setLine(_CurrentLine->line().x1(),
                              _CurrentLine->line().y1(),
                              event->scenePos().x(),
                              event->scenePos().y());
    }
}

void whiteboardcanva::mouseReleaseEvent(QGraphicsSceneMouseEvent *event)
{
    _CurrentLine = NULL;
}

void whiteboardcanva::drawCircle(int x, int y, int radius)
{
    addEllipse(x - (radius / 2), y - (radius / 2), radius, radius);
}

void whiteboardcanva::drawSquare(int x, int y, int size)
{
    addRect(x - (size / 2), y - size / 2, size, size);
}

void whiteboardcanva::SetGraphicsType(GraphicsType type)
{
    _CurrentType = type;
}
