#ifndef LINETIMELINE_H
#define LINETIMELINE_H

#include <QPen>
#include <QPainter>
#include <QWidget>

class LineTimeline : public QWidget
{
    Q_OBJECT
public:
    explicit LineTimeline(QWidget *parent = 0);
    virtual void paintEvent(QPaintEvent*);
};

#endif // LINETIMELINE_H
