#ifndef WHITEBOARDBUTTONCHOICE_H
#define WHITEBOARDBUTTONCHOICE_H

#include <QPainter>
#include <QAbstractButton>

class WhiteboardButtonChoice : public QAbstractButton
{
public:
    WhiteboardButtonChoice();
    void paintEvent(QPaintEvent*);

signals:

public slots:
};

#endif // WHITEBOARDBUTTONCHOICE_H
