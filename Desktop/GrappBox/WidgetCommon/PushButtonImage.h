#ifndef PUSHBUTTONIMAGE_H
#define PUSHBUTTONIMAGE_H

#include <QAbstractButton>
#include <QWidget>
#include <QColor>
#include <QPainter>

#include <QPaintEvent>
#include <QMouseEvent>

class PushButtonImage : public QAbstractButton
{
    Q_OBJECT
public:
    explicit PushButtonImage(QWidget *parent = 0);

    void SetImage(QPixmap pixmap);
    void SetColors(QColor onNormal, QColor onPressed, QColor onHover);

protected:
    virtual void paintEvent(QPaintEvent *e);
    virtual void enterEvent(QEvent *);
    virtual void leaveEvent(QEvent *);

signals:

public slots:

private:
    QColor _NormalColor;
    QColor _HoverColor;
    QColor _PressedColor;

    QPixmap     *_NormalImage;
    QPixmap     *_HoverImage;
    QPixmap     *_PressedImage;

    QPointF     _LastSize;

    QPixmap     *_LastPixmap;

    bool        _IsHover;

    QRect       _LastRect;
};

#endif // PUSHBUTTONIMAGE_H
