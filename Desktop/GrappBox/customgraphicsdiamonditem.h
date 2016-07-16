#ifndef CUSTOMGRAPHICSDIAMONDITEM_H
#define CUSTOMGRAPHICSDIAMONDITEM_H

#include <QPainter>
#include <QtWidgets/QGraphicsItem>

class CustomGraphicsDiamondItem : public QGraphicsItem
{
public:
    CustomGraphicsDiamondItem(qreal x, qreal y, qreal width, qreal height, const QPen &pen, const QBrush &background, QGraphicsItem *parent = nullptr);

    virtual QRectF boundingRect() const;
    void SetRect(const QRectF &rect);
    void SetRect(qreal x, qreal y, qreal width, qreal height);
    void SetPen(const QPen &pen);
    void SetBackground(const QBrush &background);

protected:
    void paint(QPainter * painter, const QStyleOptionGraphicsItem * option, QWidget * widget = 0);

signals:

public slots:

private:
    QRectF       _Rect;
    QPen         _Pen;
    QBrush       _Background;
};

#endif // CUSTOMGRAPHICSDIAMONDITEM_H
