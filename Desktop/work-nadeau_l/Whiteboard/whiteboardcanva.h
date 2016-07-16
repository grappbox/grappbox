#ifndef WHITEBOARDCANVA_H
#define WHITEBOARDCANVA_H

#include <QWidget>
#include <QGraphicsScene>
#include <QGraphicsView>
#include <QGraphicsLineItem>
#include <QGraphicsSceneMouseEvent>

enum GraphicsType
{
    LINE,
    RECT,
    CIRCLE,
    ERASE
};

class whiteboardcanva : public QGraphicsScene
{
    Q_OBJECT
public:
    explicit whiteboardcanva(QWidget *parent = 0);

    void SetGraphicsType(GraphicsType type);

signals:

public slots:

protected:
    void mouseMoveEvent(QGraphicsSceneMouseEvent *event);
    void mouseReleaseEvent(QGraphicsSceneMouseEvent *event);
    void mousePressEvent(QGraphicsSceneMouseEvent *event);

private:
    void drawCircle(int x, int y, int radius);
    void drawSquare(int x, int y, int size);

private:
    QGraphicsLineItem *_CurrentLine;
    GraphicsType _CurrentType;
};

#endif // WHITEBOARDCANVA_H
