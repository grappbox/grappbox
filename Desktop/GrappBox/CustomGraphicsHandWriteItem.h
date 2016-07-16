#ifndef CUSTOMGRAPHICSHANDWRITEITEM_H
#define CUSTOMGRAPHICSHANDWRITEITEM_H

#include <QtWidgets/QGraphicsItem>
#include <QList>
#include <QPointF>
#include <QPainter>
#include <QPen>

class CustomGraphicsHandWriteItem : public QGraphicsItem
{
public:
    CustomGraphicsHandWriteItem(const QPen &pen, QGraphicsItem *parent = nullptr);

    virtual QRectF boundingRect() const;
    void Append(qreal x, qreal y);
    void SetPen(const QPen &pen);

protected:
    void paint(QPainter * painter, const QStyleOptionGraphicsItem * option, QWidget * widget = 0);

signals:

public slots:

private:
    QList<QPointF>       _Points;
    QPen                 _Pen;
};

#endif // CUSTOMGRAPHICSHANDWRITEITEM_H
