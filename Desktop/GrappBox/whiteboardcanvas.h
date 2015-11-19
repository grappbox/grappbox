#ifndef WHITEBOARDCANVAS_H
#define WHITEBOARDCANVAS_H

#include <QWidget>
#include <QGraphicsScene>
#include <QGraphicsView>
#include <QGraphicsLineItem>
#include <QGraphicsSceneMouseEvent>
#include <QPen>
#include <QMap>
#include <QStack>

#include "customgraphicsdiamonditem.h"
#include "CustomGraphicsHandWriteItem.h"
#include "BodyWhiteboardWritingText.h"

enum GraphicsType
{
    LINE = 0,
    RECT = 1,
    CIRCLE = 2,
    LOZENGE = 3,
    HAND_WRITE = 5,
    TEXT = 6,
    ERASER = 8,
    NONE = -1
};

class WhiteboardCanvas : public QGraphicsScene
{
    Q_OBJECT
public:
    WhiteboardCanvas(QWidget *parent = 0);

    void SetGraphicsType(GraphicsType type);
    void SetBrushColor(const QColor &col);
    void SetBrushWidth(qreal width);
    void SetBackgroundColor(const QColor &col);

signals:

public slots:
    void OnTextPopupCancel();
    void OnTextPopupAdd(QString str, bool italic, bool bold, int size);

protected:
    void mouseMoveEvent(QGraphicsSceneMouseEvent *event);
    void mouseReleaseEvent(QGraphicsSceneMouseEvent *event);
    void mousePressEvent(QGraphicsSceneMouseEvent *event);
    void drawBackground(QPainter *painter, const QRectF &rect);

private:
    void DeleteCurrentObjectDraw();
    void DrawCircle(int x, int y, int radius);
    void DrawSquare(int x, int y, int size);

private:
    QGraphicsLineItem *_CurrentLine;
    QGraphicsEllipseItem *_CurrentEllipse;
    QGraphicsRectItem *_CurrentRect;
    CustomGraphicsDiamondItem *_CurrentDiamond;
    CustomGraphicsHandWriteItem *_CurrentHandWriting;
    GraphicsType _CurrentType;
    QPointF _StartPoint;
    QColor _BrushColor;
    qreal _BrushWidth;
    QColor _BackgroundColor;
    BodyWhiteboardWritingText *_Popup;
    QPointF _MouseRightClickPoint;
    QMap<int, QGraphicsItem*>   _ItemMap;
    QStack<QGraphicsItem*> _ItemStacked;
};

#endif // WHITEBOARDCANVAS_H
