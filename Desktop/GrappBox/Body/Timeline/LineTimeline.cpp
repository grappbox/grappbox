#include "LineTimeline.h"

LineTimeline::LineTimeline(QWidget *parent) : QWidget(parent)
{
    setFixedWidth(50);
    setMinimumHeight(80);
}

void LineTimeline::paintEvent(QPaintEvent*)
{
    QPainter p(this);
    p.setRenderHint(QPainter::Antialiasing, true);
    QBrush brush(QColor(68, 68, 68));
    QPen pen(brush, 5);
    pen.setColor(QColor(48, 48, 48));

    p.setPen(pen);
    p.drawLine(QPointF(25, 0), QPointF(25, 30));
    p.drawEllipse(QRectF(20, 35, 10, 10));
    p.drawLine(QPointF(25, 50), QPointF(25, height()));
}
