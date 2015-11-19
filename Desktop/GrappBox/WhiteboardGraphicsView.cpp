#include <QDebug>
#include "WhiteboardGraphicsView.h"

WhiteboardGraphicsView::WhiteboardGraphicsView(QWidget *parent) : QGraphicsView(parent)
{
}

void WhiteboardGraphicsView::contextMenuEvent(QContextMenuEvent *event)
{
    QMenu dropDownMenu;
    dropDownMenu.addAction("Line");
    dropDownMenu.addAction("Square");
    dropDownMenu.addAction("Ellipse");
    dropDownMenu.addAction("Losange");
    dropDownMenu.addSeparator();
    dropDownMenu.addAction("Hand write");
    dropDownMenu.addAction("Add text");
    dropDownMenu.addSeparator();
    dropDownMenu.addAction("Erase");

    QAction *selectedItem = dropDownMenu.exec(event->globalPos());
    if (selectedItem)
    {
        emit OnMenuAction(dropDownMenu.actions().indexOf(selectedItem));
    }
}

void WhiteboardGraphicsView::mousePressEvent(QMouseEvent *event)
{
    if (event->button() == Qt::MiddleButton)
    {
        _IsDragging = true;
        _StartPointDrag = event->globalPos();
    }
    QGraphicsView::mousePressEvent(event);
}

void WhiteboardGraphicsView::mouseMoveEvent(QMouseEvent *event)
{
    QPoint deltaDrag = _StartPointDrag - event->globalPos();
    centerOn(this->mapFromGlobal(deltaDrag));
    QGraphicsView::mouseMoveEvent(event);
}

void WhiteboardGraphicsView::mouseReleaseEvent(QMouseEvent *event)
{
    if (event->button() == Qt::MiddleButton)
        _IsDragging = false;
    QGraphicsView::mouseReleaseEvent(event);
}

void WhiteboardGraphicsView::wheelEvent(QWheelEvent *ZoomEvent)
{
    setTransformationAnchor(QGraphicsView::AnchorUnderMouse);
    static const double scaleFactor = 1.15;
    static double currentScale = 1.0;
    static const double scaleMin = .1;

    if(ZoomEvent->delta() > 0)
    {
        scale(scaleFactor, scaleFactor);
        currentScale *= scaleFactor;
    }
    else if (currentScale > scaleMin)
    {
        scale(1 / scaleFactor, 1 / scaleFactor);
        currentScale /= scaleFactor;
    }
}
