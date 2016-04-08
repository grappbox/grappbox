#ifndef GANTTARROW_H
#define GANTTARROW_H

#include <QObject>
#include <QPainter>
#include <QtQuick>
#include <QQuickPaintedItem>
#include <QDate>

class GanttView : public QQuickPaintedItem
{
    Q_OBJECT

    Q_PROPERTY(float sizeX READ sizeX WRITE setSizeX NOTIFY sizeXChanged)
    Q_PROPERTY(float sizeY READ sizeY WRITE setSizeY NOTIFY sizeYChanged)
    Q_PROPERTY(float minSizeWeek READ minSizeWeek WRITE setMinSizeWeek NOTIFY minSizeWeekChanged)
    Q_PROPERTY(float minSizeYear READ minSizeYear WRITE setMinSizeYear NOTIFY minSizeYearChanged)
    Q_PROPERTY(QColor rectangleColor READ rectangleColor WRITE setRectangleColor NOTIFY rectangleColorChanged)
    Q_PROPERTY(float cursorY READ cursorY WRITE setCursorY NOTIFY cursorYChanged)
    Q_PROPERTY(float cursorX READ cursorX WRITE setCursorX NOTIFY cursorXChanged)
    Q_PROPERTY(int numberOfDraw READ numberOfDraw WRITE setNumberOfDraw NOTIFY numberOfDrawChanged)

public:
    explicit GanttView(QQuickItem *parent = 0);

    virtual void paint(QPainter *painter);

    // Setter
    void setSizeX(float value);
    void setSizeY(float value);
    void setMinSizeWeek(float value);
    void setMinSizeYear(float value);
    void setRectangleColor(QColor value);
    void setCursorY(float value);
    void setCursorX(float value);
    void setNumberOfDraw(int value);

    // Getter
    float sizeX() const;
    float sizeY() const;
    float minSizeWeek() const;
    float minSizeYear() const;
    QColor rectangleColor() const;
    float cursorY() const;
    float cursorX() const;
    int numberOfDraw() const;

signals:
    // Notification
    void sizeXChanged();
    void sizeYChanged();
    void minSizeWeekChanged();
    void minSizeYearChanged();
    void rectangleColorChanged();
    void cursorYChanged();
    void cursorXChanged();
    void numberOfDrawChanged();

public slots:

private:
    void DrawGrid(QPainter *painter);
    void DrawDate(QPainter *painter);
    void DrawTaskRectangles(QPainter *painter);
    void DrawArrow(QPainter *painter);

    float _SizeX;
    float _SizeY;
    float _MinSizeWeek;
    float _MinSizeYear;
    QColor _RectangleColor;
    float _CursorX;
    float _CursorY;

    int _NumberOfDraw;

    QDate _TodayDate;
};

#endif // GANTTARROW_H
