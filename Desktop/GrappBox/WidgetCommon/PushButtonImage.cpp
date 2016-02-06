#include <QBitmap>
#include "PushButtonImage.h"
#include <QDebug>

PushButtonImage::PushButtonImage(QWidget *parent) : QAbstractButton(parent)
{
    _NormalColor = QColor(80, 80, 80);
    _PressedColor = QColor(60, 60, 60);
    _HoverColor = QColor(120, 0, 120);
    _LastPixmap = nullptr;
    _IsHover = false;
    setMouseTracking(true);
    setAttribute(Qt::WA_Hover, true);
    setSizePolicy(QSizePolicy::Expanding, QSizePolicy::Expanding);
}

void PushButtonImage::enterEvent(QEvent * event)
{
    _IsHover = true;
    repaint();
    QWidget::enterEvent(event);
}

void PushButtonImage::leaveEvent(QEvent * event)
{
    _IsHover = false;
    repaint();
    QWidget::leaveEvent(event);
}

void PushButtonImage::paintEvent(QPaintEvent *e)
{
    _LastRect = e->rect();
    if (_LastPixmap == nullptr)
        return;
    QPainter p(this);
    p.setRenderHint(QPainter::Antialiasing, true);
    QRect rect = e->rect();
    float size = (rect.height() < rect.width()) ? rect.height() : rect.width();
    float posX = rect.width() / 2 - (size / 2) + rect.x();
    if (isDown())
    {
        QPixmap map = _PressedImage->scaled((int)size, (int)size);
        p.drawPixmap(posX, rect.y(), size, size, map);
    }
    else if (_IsHover)
    {
        QPixmap map = _HoverImage->scaled((int)size, (int)size);
        p.drawPixmap(posX, rect.y(), size, size, map);
    }
    else
    {
        QPixmap map = _NormalImage->scaled((int)size, (int)size);
        p.drawPixmap(posX, rect.y(), size, size, map);
    }
}

void PushButtonImage::SetImage(QPixmap pixmap)
{
    _LastPixmap = new QPixmap(pixmap);
    SetColors(_NormalColor, _PressedColor, _HoverColor);
}

void PushButtonImage::SetColors(QColor onNormal, QColor onPressed, QColor onHover)
{
    _NormalColor = onNormal;
    _PressedColor = onPressed;
    QImage imgN = _LastPixmap->toImage();
    QImage imgP = _LastPixmap->toImage();
    QImage imgH = _LastPixmap->toImage();
    QImage alpha = imgN.alphaChannel();
    for (int i = 0; i < imgN.height() * imgN.width(); i++)
    {
        QPoint p(i % imgN.width(), i / imgN.width());
        QColor pColor = QColor(imgN.pixel(p));
        pColor.setRedF(pColor.redF() * onNormal.redF());
        pColor.setGreenF(pColor.greenF() * onNormal.greenF());
        pColor.setBlueF(pColor.blueF() * onNormal.blueF());
        imgN.setPixel(p, pColor.rgba());
        pColor = QColor(imgP.pixel(p));
        pColor.setRedF(pColor.redF() * onPressed.redF());
        pColor.setGreenF(pColor.greenF() * onPressed.greenF());
        pColor.setBlueF(pColor.blueF() * onPressed.blueF());
        imgP.setPixel(p, pColor.rgba());
        pColor = QColor(imgH.pixel(p));
        pColor.setRedF(pColor.redF() * onHover.redF());
        pColor.setGreenF(pColor.greenF() * onHover.greenF());
        pColor.setBlueF(pColor.blueF() * onHover.blueF());
        imgH.setPixel(p, pColor.rgba());
    }
    imgN.setAlphaChannel(alpha);
    imgP.setAlphaChannel(alpha);
    imgH.setAlphaChannel(alpha);
    _NormalImage = new QPixmap(QPixmap::fromImage(imgN));
    _PressedImage = new QPixmap(QPixmap::fromImage(imgP));
    _HoverImage = new QPixmap(QPixmap::fromImage(imgH));
    repaint();
}
