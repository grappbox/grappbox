#ifndef WHITEBOARDGRAPHICSVIEW_H
#define WHITEBOARDGRAPHICSVIEW_H

#include <QPoint>
#include <QtWidgets/QMenu>
#include <QtWidgets/QAction>
#include <QtWidgets/QGraphicsView>
#include <QContextMenuEvent>
#include <QWheelEvent>
#include <QPoint>

class WhiteboardGraphicsView : public QGraphicsView
{
    Q_OBJECT
public:
    WhiteboardGraphicsView(QWidget *parent = nullptr);

protected:
    void contextMenuEvent(QContextMenuEvent *event);
    void mousePressEvent(QMouseEvent *event);
    void mouseMoveEvent(QMouseEvent *event);
    void mouseReleaseEvent(QMouseEvent *event);
    void wheelEvent(QWheelEvent *ZoomEvent);

signals:
    void OnMenuAction(int id);

public slots:

private:
    bool _IsDragging;
    QPoint  _StartPointDrag;
};

#endif // WHITEBOARDGRAPHICSVIEW_H
