#ifndef WHITEBOARDCONTROLLER_H
#define WHITEBOARDCONTROLLER_H

#include <QObject>

class WhiteboardController : public QObject
{
    Q_OBJECT

    Q_ENUMS(WhiteboardMode)


public:

    enum WhiteboardMode {
        MOVE,
        TEXT,
        LINE,
        RECT,
        ELLIPSE,
        DIAMOND,
        HANDWRITE
    };

    explicit WhiteboardController(QObject *parent = 0);

signals:

public slots:
};

#endif // WHITEBOARDCONTROLLER_H
