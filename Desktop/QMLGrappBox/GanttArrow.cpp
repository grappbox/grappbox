#include "GanttArrow.h"

GanttArrow::GanttArrow(QQuickItem *parent) : QQuickPaintedItem(parent)
{

}

void GanttArrow::paint(QPainter *painter)
{
    QPen pen(QColor(255, 0, 0), 2);
    painter->setPen(pen);
    painter->setRenderHints(QPainter::Antialiasing, true);
    this->boundingRect();
}
