#ifndef GANTTARROW_H
#define GANTTARROW_H

#include <QObject>
#include <QPainter>
#include <QtQuick>

class GanttArrow : public QQuickPaintedItem
{
    Q_OBJECT
public:
    explicit GanttArrow(QQuickItem *parent = 0);

    virtual void paint(QPainter *painter);

signals:

public slots:
};

#endif // GANTTARROW_H
