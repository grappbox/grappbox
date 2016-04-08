#include <QDebug>
#include "GanttView.h"

GanttView::GanttView(QQuickItem *parent) : QQuickPaintedItem(parent)
{
    _TodayDate = QDate::currentDate();
    qDebug() << "Gantt view opened";
}

void GanttView::paint(QPainter *painter)
{
    painter->setRenderHints(QPainter::Antialiasing, true);
    DrawGrid(painter);
    DrawDate(painter);
}

void GanttView::DrawGrid(QPainter *painter)
{
    int diff = (int)(_CursorX / 100);
    QPen pen(QColor(0.8, 0.8, 0.8), 0.5);
    painter->setPen(pen);
    QRectF maxRect = this->boundingRect();
    float sizeCase = _SizeX;
    float startY = _SizeY * 3;
    if (_SizeX <= _MinSizeWeek)
    {
        startY -= _SizeY;
        sizeCase *= 7;
    }
    if (_SizeX <= _MinSizeYear)
    {
        startY -= _SizeY;
        sizeCase *= 4;
    }
    float currentDrawX = (maxRect.center().x() - (sizeCase / 2.0f)) + (_CursorX - (float)(diff * 100));
    for (int i = _NumberOfDraw / -2; i < _NumberOfDraw / 2; ++i)
    {
        float drawX = currentDrawX + (float)i * sizeCase;
        painter->drawLine(QPointF(drawX, startY), QPointF(drawX, maxRect.height()));
    }
}

void GanttView::DrawDate(QPainter *painter)
{
    QDate currentDateCursor = _TodayDate;
    int diff = (int)(_CursorX / 100);
    currentDateCursor = currentDateCursor.addDays(-diff);
    float sizeCase = _SizeX;
    if (_SizeX <= _MinSizeWeek)
        sizeCase *= 7;
    if (_SizeX <= _MinSizeYear)
        sizeCase *= 4;
    QRectF maxRect = this->boundingRect();
    float currentDrawX = (maxRect.center().x() - (sizeCase / 2.0f)) + (_CursorX - (float)(diff * 100));
    float currentDrawY = _SizeY;
    // Draw Month
    for (int i = _NumberOfDraw / -2; i < _NumberOfDraw / 2; ++i)
    {
        float drawX;
        QRectF textContain;
        QDate current = currentDateCursor.addDays(i);
        if (i == _NumberOfDraw / -2)
        {
            QDate newDate;
            newDate.setDate(current.year(), current.month(), 1);
            int dayDiff = newDate.daysTo(current);
            drawX = currentDrawX + (float)(i - dayDiff) * sizeCase;
            textContain = QRectF(drawX, 0, sizeCase * newDate.daysInMonth(), _SizeY);
        }
        else if (current.day() == 1)
        {
            drawX = currentDrawX + (float)i * sizeCase;
            textContain = QRectF(drawX, 0, sizeCase * current.daysInMonth(), _SizeY);
        }
        else
            continue;
        painter->fillRect(textContain, QColor(210, 210, 210));
        QPen pen(QColor(0, 0, 0), 0.5);
        painter->setPen(pen);
        painter->drawText(textContain, Qt::AlignCenter , current.toString("MMMM yyyy"));
    }
    // Draw weeks
    if (_SizeX > _MinSizeYear)
    {
        for (int i = _NumberOfDraw / -2; i < _NumberOfDraw / 2; ++i)
        {
            float drawX;
            QRectF textContain;
            QDate current = currentDateCursor.addDays(i);
            if (i == _NumberOfDraw / -2)
            {
                QDate newDate = current;
                while (newDate.dayOfWeek() != 1)
                    newDate = newDate.addDays(-1);
                int dayDiff = newDate.daysTo(current);
                drawX = currentDrawX + (float)(i - dayDiff) * sizeCase;
                textContain = QRectF(drawX, _SizeY, sizeCase * 7, _SizeY);
            }
            else if (current.dayOfWeek() == 1)
            {
                drawX = currentDrawX + (float)i * sizeCase;
                textContain = QRectF(drawX, _SizeY, sizeCase * 7, _SizeY);
            }
            else
                continue;
            painter->fillRect(textContain, QColor(200, 200, 200));
            QPen pen(QColor(0, 0, 0), 0.5);
            painter->setPen(pen);
            painter->drawText(textContain, Qt::AlignCenter , "Week #" + QVariant(current.weekNumber()).toString());
        }
        currentDrawY += _SizeY;
    }
    // Draw days
    if (_SizeX > _MinSizeWeek)
    {

        for (int i = _NumberOfDraw / -2; i < _NumberOfDraw / 2; ++i)
        {
            float drawX = currentDrawX + (float)i * sizeCase;
            QRectF textContain = QRectF(drawX, currentDrawY, sizeCase, _SizeY);
            painter->fillRect(textContain, QColor(210, 210, 210));
            QDate current = currentDateCursor.addDays(i);
            QPen pen(QColor(0, 0, 0), 0.5);
            painter->setPen(pen);
            painter->drawText(textContain, Qt::AlignCenter , current.toString("MM/dd"));
        }
    }
}

void GanttView::DrawTaskRectangles(QPainter *painter)
{

}

void GanttView::DrawArrow(QPainter *painter)
{

}

void GanttView::setSizeX(float value)
{
    _SizeX = value;
    update();
    emit sizeXChanged();
}

void GanttView::setSizeY(float value)
{
    _SizeY = value;
    emit sizeYChanged();
}

void GanttView::setMinSizeWeek(float value)
{
    _MinSizeWeek = value;
    emit minSizeWeekChanged();
}

void GanttView::setMinSizeYear(float value)
{
    _MinSizeYear = value;
    emit minSizeYearChanged();
}

void GanttView::setRectangleColor(QColor value)
{
    _RectangleColor = value;
    emit rectangleColorChanged();
}

void GanttView::setCursorY(float value)
{
    _CursorY = value;
    emit cursorYChanged();
}

void GanttView::setCursorX(float value)
{
    _CursorX = value;
    update();
    emit cursorXChanged();
}

void GanttView::setNumberOfDraw(int value)
{
    _NumberOfDraw = value;
    emit numberOfDrawChanged();
}

float GanttView::sizeX() const
{
    return _SizeX;
}

float GanttView::sizeY() const
{
    return _SizeY;
}

float GanttView::minSizeWeek() const
{
    return _MinSizeWeek;
}

float GanttView::minSizeYear() const
{
    return _MinSizeYear;
}

QColor GanttView::rectangleColor() const
{
    return _RectangleColor;
}

float GanttView::cursorY() const
{
    return _CursorY;
}

float GanttView::cursorX() const
{
    return _CursorX;
}

int GanttView::numberOfDraw() const
{
    return _NumberOfDraw;
}
