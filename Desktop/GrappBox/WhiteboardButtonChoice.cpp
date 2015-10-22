#include "WhiteboardButtonChoice.h"

WhiteboardButtonChoice::WhiteboardButtonChoice()
{

}

void WhiteboardButtonChoice::paintEvent(QPaintEvent *event)
{
    QPainter paint(this);
    paint.drawRoundRect(0, 0, size().width(), size().height(), 10, 10);
}
