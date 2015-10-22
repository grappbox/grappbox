#include "whiteboardcanvas.h"
#include <QDebug>

WhiteboardCanvas::WhiteboardCanvas(QWidget *parent) : QGraphicsScene(parent)
{
    _CurrentType = RECT;
    _CurrentLine = NULL;
    _CurrentEllipse = NULL;
    _CurrentRect = NULL;
    _CurrentDiamond = NULL;
    _CurrentHandWriting = NULL;
    _BrushColor = QColor(0, 0, 0);
    _BrushWidth = 5;
    _BackgroundColor = QColor(128, 100, 0, 255);
}

void WhiteboardCanvas::mousePressEvent(QGraphicsSceneMouseEvent *event)
{
    if (event->button() != Qt::LeftButton)
    {
        DeleteCurrentObjectDraw();
        return;
    }
    QPointF mousePos = event->scenePos();
    QPen pen( QPen(QBrush(_BrushColor), _BrushWidth));
    QBrush backgroud(_BackgroundColor);
    qDebug() << "Current Type = " << _CurrentType;
    switch (_CurrentType)
    {
        case LINE:
        _CurrentLine = addLine(mousePos.x(), mousePos.y(), mousePos.x(), mousePos.y(), pen);
        break;
        case RECT:
        _CurrentRect = addRect(mousePos.x(), mousePos.y(), 1, 1, pen, backgroud);
        _StartPoint = mousePos;
        break;
        case CIRCLE:
        _CurrentEllipse = addEllipse(mousePos.x(), mousePos.y(), 1, 1, pen, backgroud);
        _StartPoint = mousePos;
        break;
        case LOZENGE:
        _CurrentDiamond = new CustomGraphicsDiamondItem(mousePos.x(), mousePos.y(), 50, 50, pen, backgroud);
        addItem(_CurrentDiamond);
        _StartPoint = mousePos;
        break;
        case HAND_WRITE:
        _CurrentHandWriting = new CustomGraphicsHandWriteItem(pen);
        addItem(_CurrentHandWriting);
        break;
        case ERASER:
        break;
    }
}

void WhiteboardCanvas::DeleteCurrentObjectDraw()
{
    if (_CurrentLine)
        this->removeItem(_CurrentLine);
    if (_CurrentEllipse)
        this->removeItem(_CurrentEllipse);
    if (_CurrentRect)
        this->removeItem(_CurrentRect);
    if (_CurrentDiamond)
        this->removeItem(_CurrentDiamond);
    if (_CurrentHandWriting)
        this->removeItem(_CurrentHandWriting);
}

void WhiteboardCanvas::mouseMoveEvent(QGraphicsSceneMouseEvent *event)
{
    if (_CurrentLine)
    {
        _CurrentLine->setLine(_CurrentLine->line().x1(),
                              _CurrentLine->line().y1(),
                              event->scenePos().x(),
                              event->scenePos().y());
    }
    if (_CurrentEllipse)
    {
        qreal x, y, width, height;
        x = _StartPoint.x();
        y = _StartPoint.y();
        width = event->scenePos().x() - x;
        height = event->scenePos().y() - y;
        _CurrentEllipse->setRect(x, y, width, height);
    }
    if (_CurrentRect)
    {
        qreal x, y, width, height;
        x = _StartPoint.x();
        y = _StartPoint.y();
        width = event->scenePos().x() - x;
        height = event->scenePos().y() - y;
        if (width < 0)
        {
            x += width;
            width = -width;
        }
        if (height < 0)
        {
            y += height;
            height = -height;
        }
        _CurrentRect->setRect(x, y, width, height);
    }
    if (_CurrentDiamond)
    {
        qreal x, y, width, height;
        x = _StartPoint.x();
        y = _StartPoint.y();
        width = event->scenePos().x() - x;
        height = event->scenePos().y() - y;
        if (width < 0)
        {
            x += width;
            width = -width;
        }
        if (height < 0)
        {
            y += height;
            height = -height;
        }
        _CurrentDiamond->SetRect(x, y, width, height);
    }
    if (_CurrentHandWriting)
    {
        _CurrentHandWriting->Append(event->scenePos().x(), event->scenePos().y());
    }
    this->update();
}

void WhiteboardCanvas::mouseReleaseEvent(QGraphicsSceneMouseEvent *event)
{
    _CurrentLine = NULL;
    _CurrentEllipse = NULL;
    _CurrentRect = NULL;
    _CurrentDiamond = NULL;
    _CurrentHandWriting = NULL;
}

void WhiteboardCanvas::drawBackground(QPainter *painter, const QRectF &rect)
{
    const int gridSize = 25;

    qreal left = int(rect.left()) - (int(rect.left()) % gridSize);
    qreal top = int(rect.top()) - (int(rect.top()) % gridSize);

    QVarLengthArray<QLineF, 100> lines;

    for (qreal x = left; x < rect.right(); x += gridSize)
        lines.append(QLineF(x, rect.top(), x, rect.bottom()));
    for (qreal y = top; y < rect.bottom(); y += gridSize)
        lines.append(QLineF(rect.left(), y, rect.right(), y));
    painter->setOpacity(0.3);
    painter->drawLines(lines.data(), lines.size());
}

void WhiteboardCanvas::SetGraphicsType(GraphicsType type)
{
    if (type == NONE)
        return;
    if (type != _CurrentType)
        DeleteCurrentObjectDraw();
    _CurrentType = type;
}
