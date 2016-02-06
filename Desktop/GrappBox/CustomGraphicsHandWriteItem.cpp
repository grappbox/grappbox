#include "CustomGraphicsHandWriteItem.h"

CustomGraphicsHandWriteItem::CustomGraphicsHandWriteItem(const QPen &pen, QGraphicsItem *parent) : QGraphicsItem(parent)
{
    _Pen = pen;
}

QRectF CustomGraphicsHandWriteItem::boundingRect() const
{
    QPointF min = QPointF(0, 0);
    QPointF max = QPointF(0, 0);
    bool first = true;
    for (QList<QPointF>::const_iterator it = _Points.begin(); it != _Points.end(); ++it)
    {
        if (first)
        {
            min = *it;
            max = *it;
            continue;
        }
        if (it->x() < min.x())
            min.setX(it->x());
        if (it->x() > max.x())
            max.setX(it->x());
        if (it->y() < min.y())
            min.setY(it->y());
        if (it->y() > max.y())
            max.setY(it->y());
    }
    return QRectF(min, max);
}

void CustomGraphicsHandWriteItem::Append(qreal x, qreal y)
{
    static const float MinimumDistance = 0.5f;
    if (_Points.size() == 0 || (QPointF(x, y) - _Points.last()).manhattanLength() >= MinimumDistance)
        _Points.append(QPointF(x, y));
}

void CustomGraphicsHandWriteItem::SetPen(const QPen &pen)
{
    _Pen = pen;
}

void CustomGraphicsHandWriteItem::paint(QPainter *painter, const QStyleOptionGraphicsItem *option, QWidget *widget)
{
    if (_Points.size() <= 1)
        return;
    QList<QPointF>::iterator lastIterator = nullptr;
    painter->setPen(_Pen);
    for (QList<QPointF>::iterator it = _Points.begin(); it != _Points.end(); ++it)
    {
        if (it != _Points.begin())
        {
            painter->drawLine(*it, *lastIterator);
        }
        lastIterator = it;
    }
}
