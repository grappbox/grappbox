#include <QDebug>
#include "customgraphicsdiamonditem.h"

CustomGraphicsDiamondItem::CustomGraphicsDiamondItem(qreal x, qreal y, qreal width, qreal height, const QPen &pen, const QBrush &background, QGraphicsItem *parent) : QGraphicsItem(parent)
{
    SetRect(x, y, width, height);
    _Pen = pen;
    _Background = background;
}

QRectF CustomGraphicsDiamondItem::boundingRect() const
{
    return _Rect;
}

void CustomGraphicsDiamondItem::SetRect(const QRectF &rect)
{
    _Rect = rect;
    this->update();
}

void CustomGraphicsDiamondItem::SetRect(qreal x, qreal y, qreal width, qreal height)
{
    SetRect(QRectF(x, y, width, height));
}

void CustomGraphicsDiamondItem::SetPen(const QPen &pen)
{
    _Pen = pen;
}

void CustomGraphicsDiamondItem::SetBackground(const QBrush &background)
{
    _Background = background;
}

void CustomGraphicsDiamondItem::paint(QPainter *painter, const QStyleOptionGraphicsItem *option, QWidget *widget)
{
    QVector<QPointF> points;
    points.append(QPointF(_Rect.x(), _Rect.y() + _Rect.height() / 2));
    points.append(QPointF(_Rect.x() + _Rect.width() / 2, _Rect.y()));
    points.append(QPointF(_Rect.x() + _Rect.width(), _Rect.y() + _Rect.height() / 2));
    points.append(QPointF(_Rect.x() + _Rect.width() / 2, _Rect.y() + _Rect.height()));
    painter->setPen(_Pen);
    painter->setBrush(_Background);
    painter->drawPolygon(QPolygonF(points));
}
