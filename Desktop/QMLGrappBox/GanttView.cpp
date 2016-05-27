#include <QDebug>
#include <QtMath>
#include "GanttView.h"

GanttView::GanttView(QQuickItem *parent) : QQuickPaintedItem(parent)
{
    _TodayDate = QDate::currentDate();
    _Type = NONE;
    _LastMousePos = QPointF(-1, -1);
}

void GanttView::paint(QPainter *painter)
{
    painter->setRenderHints(QPainter::Antialiasing, true);
    DrawGrid(painter);
    DrawDate(painter);
    DrawTaskRectangles(painter);
    DrawArrow(painter);
    DrawTaskBar(painter);
    if (_Type == SET_DEPENDANCE)
        DrawNewDependenciesArrow(painter);
}

void GanttView::DrawGrid(QPainter *painter)
{
    int diff = (int)(_CursorX / _SizeX);
    QPen pen(QColor(0.8, 0.8, 0.8), 0.5);
    painter->setPen(pen);
    QRectF maxRect = this->boundingRect();
    float sizeCase = _SizeX;
    float startY = _SizeYTop * 3;
    if (_SizeX <= _MinSizeWeek)
    {
        startY -= _SizeYTop;
        sizeCase *= 7;
    }
    if (_SizeX <= _MinSizeYear)
    {
        startY -= _SizeYTop;
        sizeCase *= 4;
    }
    float currentDrawX = (maxRect.center().x() - (sizeCase / 2.0f)) + (_CursorX - (float)(diff * sizeCase));
    for (int i = _NumberOfDraw / -2; i < _NumberOfDraw / 2; ++i)
    {
        float drawX = currentDrawX + (float)i * sizeCase;
        painter->drawLine(QPointF(drawX, startY), QPointF(drawX, maxRect.height()));
    }
}

void GanttView::DrawDate(QPainter *painter)
{
    QDate currentDateCursor = _TodayDate;
    int diff = (int)(_CursorX / _SizeX);
    currentDateCursor = currentDateCursor.addDays(-diff);
    float sizeCase = _SizeX;
    int multiplicateDraw = 1;
    if (_SizeX <= _MinSizeWeek)
    {
        multiplicateDraw *= 2;
    }
    if (_SizeX <= _MinSizeYear)
    {
        multiplicateDraw *= 2;
    }
    QRectF maxRect = this->boundingRect();
    float currentDrawX = (maxRect.center().x() - (sizeCase / 2.0f)) + (_CursorX - (float)(diff * _SizeX));
    float currentDrawY = _SizeYTop;
    // Draw Month
    for (int i = _NumberOfDraw * multiplicateDraw / -2; i < _NumberOfDraw * multiplicateDraw / 2; ++i)
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
            textContain = QRectF(drawX, 0, sizeCase * newDate.daysInMonth(), _SizeYTop);
        }
        else if (current.day() == 1)
        {
            drawX = currentDrawX + (float)i * sizeCase;
            textContain = QRectF(drawX, 0, sizeCase * current.daysInMonth(), _SizeYTop);
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
        for (int i = _NumberOfDraw * multiplicateDraw / -2; i < _NumberOfDraw * multiplicateDraw / 2; ++i)
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
                textContain = QRectF(drawX, _SizeYTop, sizeCase * 7, _SizeYTop);
            }
            else if (current.dayOfWeek() == 1)
            {
                drawX = currentDrawX + (float)i * sizeCase;
                textContain = QRectF(drawX, _SizeYTop, sizeCase * 7, _SizeYTop);
            }
            else
                continue;
            painter->fillRect(textContain, QColor(200, 200, 200));
            QPen pen(QColor(0, 0, 0), 0.5);
            painter->setPen(pen);
            painter->drawText(textContain, Qt::AlignCenter , "Week #" + QVariant(current.weekNumber()).toString());
        }
        currentDrawY += _SizeYTop;
    }
    // Draw days
    if (_SizeX > _MinSizeWeek)
    {

        for (int i = _NumberOfDraw / -2; i < _NumberOfDraw / 2; ++i)
        {
            float drawX = currentDrawX + (float)i * sizeCase;
            QRectF textContain = QRectF(drawX, currentDrawY, sizeCase, _SizeYTop);
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
    QDate currentDateCursor = _TodayDate;
    int diff = (int)(_CursorX / _SizeX);
    currentDateCursor = currentDateCursor.addDays(-diff);
    float currentY = _SizeYTop * 3;
    if (_SizeX <= _MinSizeWeek)
    {
        currentY -= _SizeYTop;
    }
    if (_SizeX <= _MinSizeYear)
    {
        currentY -= _SizeYTop;
    }
    QRectF maxRect = this->boundingRect();
    float currentDrawX = (maxRect.center().x() - (_SizeX / 2.0f)) + (_CursorX - (float)(diff * _SizeX));
    for (TaskData *task : _Tasks)
    {
        int diffStart = task->startDate().daysTo(QDateTime(currentDateCursor, task->startDate().time()));
        if (task->isMilestone())
        {
            float center = (_SizeX - 6) / 2 - (_SizeY - 6) / 2;
            QRectF rect(currentDrawX - (float)diffStart * _SizeX + center + 3, currentY + 3, _SizeY - 6, _SizeY - 6);
            QPainterPath path;
            QPolygonF poly;
            poly.append(QPointF(rect.left() + rect.width() / 2, rect.top()));
            poly.append(QPointF(rect.right(), rect.top() + rect.height() / 2));
            poly.append(QPointF(rect.left() + rect.width() / 2, rect.bottom()));
            poly.append(QPointF(rect.left(), rect.top() + rect.height() / 2));
            path.addPolygon(poly);
            painter->fillPath(path, _RectangleColor);
        }
        else
        {
            int length = task->startDate().daysTo(task->dueDate()) + 1;
            QRectF rect(currentDrawX - (float)diffStart * _SizeX + 3, currentY + 3, (float)length * _SizeX - 6, _SizeY - 6);
            QPainterPath path;
            path.addRoundedRect(rect, 3, 3);
            painter->fillPath(path, _RectangleColor);
            rect.setTop(rect.top() + rect.height() - (_SizeY / 5));
            rect.setWidth(task->progression() * rect.width() / 100.0f);
            QPainterPath pathProgress;
            pathProgress.addRoundedRect(rect, 3, 3);
            painter->fillPath(pathProgress, Qt::green);
        }
        currentY += _SizeY;
    }
}

void GanttView::DrawArrow(QPainter *painter)
{
    for (TaskData *task : _Tasks)
    {
        for (QVariant var : task->dependenciesAssigned())
        {
            DependenciesData *item = qobject_cast<DependenciesData*>(var.value<DependenciesData*>());
            if (item == nullptr)
                continue;
            QLine *lines;
            TaskData *targetTask = GetTaskByID(item->linkedTask());
            if (targetTask == nullptr)
                continue;
            QPoint start;
            QPoint end;
            switch (item->type())
            {
            case DependenciesData::FINISH_TO_START:
                start = GetEndAnchorTask(task);
                end = GetStartAnchorTask(targetTask);
                break;

            case DependenciesData::START_TO_START:
                start = GetStartAnchorTask(task);
                end = GetStartAnchorTask(targetTask);
                break;

            case DependenciesData::FINISH_TO_FINISH:
                start = GetEndAnchorTask(task);
                end = GetEndAnchorTask(targetTask);
                break;

            case DependenciesData::START_TO_FINISH:
                start = GetEndAnchorTask(task);
                end = GetStartAnchorTask(targetTask);
                break;
            }
            QList<QPoint> _Points;
            if (end.x() - start.x() < _SizeX)
            {
                float y = end.y() - _SizeY / 2;
                if (start.y() > end.y())
                    y = end.y() + _SizeY / 2;
                _Points.push_back(start);
                _Points.push_back(start + QPoint(_SpaceCutArrow, 0));
                _Points.push_back(QPoint(_Points.last().x(), y));
                _Points.push_back(end + QPoint(-_SpaceCutArrow, y - end.y()));
                _Points.push_back(end - QPoint(_SpaceCutArrow, 0));
                _Points.push_back(end);
            }
            else
            {
                _Points.push_back(start);
                _Points.push_back(QPoint(end.x() - _SpaceCutArrow, start.y()));
                _Points.push_back(end - QPoint(_SpaceCutArrow, 0));
                _Points.push_back(end);
            }
            lines = new QLine[_Points.size() - 1];
            for (int i = 0; i < _Points.size() - 1; ++i)
            {
                lines[i] = QLine(_Points[i], _Points[i + 1]);
            }
            painter->drawLines(lines, _Points.size() - 1);
            QPolygon poly;
            poly.push_back(end);
            poly.push_back(end - QPoint(_SpaceCutArrow / 2, _SpaceCutArrow / 3));
            poly.push_back(end + QPoint(-_SpaceCutArrow / 2, _SpaceCutArrow / 3));
            QPainterPath path;
            path.addPolygon(poly);
            painter->fillPath(path, QColor(0, 0, 0));
        }
    }
}

void GanttView::DrawTaskBar(QPainter *painter)
{
    float currentY = _SizeYTop * 3;
    if (_SizeX <= _MinSizeWeek)
    {
        currentY -= _SizeYTop;
    }
    if (_SizeX <= _MinSizeYear)
    {
        currentY -= _SizeYTop;
    }
    QRectF maxRect = this->boundingRect();
    maxRect.setWidth(_SizeTaskSlideBar);
    int currentTask = 0;
    painter->fillRect(maxRect, QColor(220, 220, 220));
    QTextOption option;
    option.setAlignment(Qt::AlignLeft | Qt::AlignVCenter);
    option.setWrapMode(QTextOption::NoWrap);
    QFont font;
    font.setPixelSize(18);
    painter->setFont(font);
    for (TaskData *task : _Tasks)
    {
        painter->drawText(QRectF(20, currentY + currentTask * _SizeY, _SizeTaskSlideBar - 20, _SizeY - 4), task->title(), option);
        painter->drawLine(QPointF(10, currentY + currentTask * _SizeY + _SizeY - 2),
                          QPointF(_SizeTaskSlideBar - 10, currentY + currentTask * _SizeY + _SizeY - 2));
        currentTask++;
    }
    maxRect.setHeight(currentY);
    painter->fillRect(maxRect, QColor(220, 220, 220));
}

void GanttView::DrawNewDependenciesArrow(QPainter *painter)
{
    QLineF line(_StartMousePos, _LastMousePos);
    painter->drawLine(line);
}

void GanttView::SetDependance(QPointF mousePos)
{
    TaskData *associatedData = nullptr;
    QDate currentDateCursor = _TodayDate;
    int diff = (int)(_CursorX / _SizeX);
    currentDateCursor = currentDateCursor.addDays(-diff);
    float currentY = _SizeYTop * 3;
    if (_SizeX <= _MinSizeWeek)
    {
        currentY -= _SizeYTop;
    }
    if (_SizeX <= _MinSizeYear)
    {
        currentY -= _SizeYTop;
    }
    QRectF maxRect = this->boundingRect();
    float currentDrawX = (maxRect.center().x() - (_SizeX / 2.0f)) + (_CursorX - (float)(diff * _SizeX));
    for (TaskData *task : _Tasks)
    {
        int diffStart = task->startDate().daysTo(QDateTime(currentDateCursor, task->startDate().time()));
        if (task->isMilestone())
        {
            float center = (_SizeX) / 2 - (_SizeY) / 2;
            QRectF rect(currentDrawX - (float)diffStart * _SizeX + center, currentY, _SizeY, _SizeY);
            if (rect.contains(mousePos))
            {
                associatedData = task;
                break;
            }
        }
        else
        {
            int length = task->startDate().daysTo(task->dueDate()) + 1;
            QRectF rect(currentDrawX - (float)diffStart * _SizeX, currentY, (float)length * _SizeX, _SizeY);
            qDebug() << rect << " : " << mousePos;
            if (rect.contains(mousePos))
            {
                associatedData = task;
                break;
            }
        }
        currentY += _SizeY;
    }
    if (associatedData != nullptr && associatedData != _SelectedTaskData)
    {
        QVariantList dd = _SelectedTaskData->dependenciesAssigned();
        dd.push_back(qVariantFromValue(new DependenciesData(DependenciesData::FINISH_TO_START, associatedData->id())));
        _SelectedTaskData->setDependenceiesAssigned(dd);
    }
}

void GanttView::SetProgression(QPointF mousePos)
{
    QDate currentDateCursor = _TodayDate;
    int diff = (int)(_CursorX / _SizeX);
    currentDateCursor = currentDateCursor.addDays(-diff);
    float currentY = _SizeYTop * 3;
    if (_SizeX <= _MinSizeWeek)
    {
        currentY -= _SizeYTop;
    }
    if (_SizeX <= _MinSizeYear)
    {
        currentY -= _SizeYTop;
    }
    QRectF maxRect = this->boundingRect();
    int diffStart = _SelectedTaskData->startDate().daysTo(QDateTime(currentDateCursor, _SelectedTaskData->startDate().time()));
    int length = _SelectedTaskData->startDate().daysTo(_SelectedTaskData->dueDate()) + 1;
    float currentDrawX = (maxRect.center().x() - (_SizeX / 2.0f)) + (_CursorX - (float)(diff * _SizeX));
    float x = currentDrawX - (float)diffStart * _SizeX;
    float xMax = (float)length * _SizeX;
    float currentProgression = mousePos.x() - x;
    if (currentProgression < 0)
        currentProgression = 0;
    if (currentProgression > xMax)
        currentProgression = xMax;
    currentProgression = (float)(qCeil(currentProgression / xMax * 10));
    _SelectedTaskData->setProgression(currentProgression * 10);
    update();
}

void GanttView::SetDeadline(QPointF mousePos)
{
    QDate currentDateCursor = _TodayDate;
    int diff = (int)(_CursorX / _SizeX);
    currentDateCursor = currentDateCursor.addDays(-diff);
    float currentY = _SizeYTop * 3;
    if (_SizeX <= _MinSizeWeek)
    {
        currentY -= _SizeYTop;
    }
    if (_SizeX <= _MinSizeYear)
    {
        currentY -= _SizeYTop;
    }
    QRectF maxRect = this->boundingRect();
    int diffStart = _SelectedTaskData->startDate().daysTo(QDateTime(currentDateCursor, _SelectedTaskData->startDate().time()));
    float currentDrawX = (maxRect.center().x() - (_SizeX / 2.0f)) + (_CursorX - (float)(diff * _SizeX));
    float x = currentDrawX - (float)diffStart * _SizeX;
    float currentProgression = mousePos.x() - x;
    int numberOfDay = qCeil(currentProgression / _SizeX);
    if (numberOfDay < 1)
        numberOfDay = 1;
    qDebug() << "Number of day : " << numberOfDay;
    QDateTime currentDate = _SelectedTaskData->dueDate();
    qDebug() << "Before date" << currentDate;
    QDateTime startDate = _SelectedTaskData->startDate();
    int beforeNumberOfDay = startDate.daysTo(currentDate);
    currentDate = currentDate.addDays(numberOfDay - beforeNumberOfDay - 1);
    _SelectedTaskData->setDueDate(currentDate);
    qDebug() << "New due date : " << currentDate;
    update();
}

QPoint GanttView::GetStartAnchorTask(TaskData *task)
{
    QRectF maxRect = this->boundingRect();
    QDate currentDateCursor = _TodayDate;
    int diff = (int)(_CursorX / _SizeX);
    currentDateCursor = currentDateCursor.addDays(-diff);
    float currentDrawX = (maxRect.center().x() - (_SizeX / 2.0f)) + (_CursorX - (float)(diff * _SizeX));
    int diffStart = task->startDate().daysTo(QDateTime(currentDateCursor, task->startDate().time()));
    float center = (_SizeX - 6) / 2 - (_SizeY - 6) / 2;
    float currentY = _SizeYTop * 3;
    if (_SizeX <= _MinSizeWeek)
    {
        currentY -= _SizeYTop;
    }
    if (_SizeX <= _MinSizeYear)
    {
        currentY -= _SizeYTop;
    }
    for (TaskData *item : _Tasks)
    {
        if (task == item)
            break;
        currentY += _SizeY;
    }
    currentY += _SizeY / 2;
    if (task->isMilestone())
        return QPoint(currentDrawX - (float)diffStart * _SizeX + center + 3, currentY);
    else
        return QPoint(currentDrawX - (float)diffStart * _SizeX + 3, currentY);
}

QPoint GanttView::GetEndAnchorTask(TaskData *task)
{
    QRectF maxRect = this->boundingRect();
    QDate currentDateCursor = _TodayDate;
    int diff = (int)(_CursorX / _SizeX);
    currentDateCursor = currentDateCursor.addDays(-diff);
    float currentDrawX = (maxRect.center().x() - (_SizeX / 2.0f)) + (_CursorX - (float)(diff * _SizeX));
    int diffStart = task->startDate().daysTo(QDateTime(currentDateCursor, task->startDate().time()));
    float center = (_SizeX - 6) / 2 - (_SizeY - 6) / 2;
    int length = task->startDate().daysTo(task->dueDate()) + 1;
    float currentY = _SizeYTop * 3;
    if (_SizeX <= _MinSizeWeek)
    {
        currentY -= _SizeYTop;
    }
    if (_SizeX <= _MinSizeYear)
    {
        currentY -= _SizeYTop;
    }
    for (TaskData *item : _Tasks)
    {
        if (task == item)
            break;
        currentY += _SizeY;
    }
    currentY += _SizeY / 2;
    if (task->isMilestone())
        return QPoint(currentDrawX - (float)diffStart * _SizeX + center + 3 + (_SizeY - 6), currentY);
    else
        return QPoint(currentDrawX - (float)diffStart * _SizeX + 3 + (float)length * _SizeX - 6, currentY);
}

TaskData *GanttView::GetTaskByID(int id)
{
    for (TaskData *task : _Tasks)
    {
        if (task && task->id() == id)
            return task;
    }
    return nullptr;
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

void GanttView::setSizeYTop(float value)
{
    _SizeYTop = value;
    emit sizeYTopChanged();
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

void GanttView::setSpaceTask(float value)
{
    _SpaceTask = value;
    emit spaceTaskChanged();
}

void GanttView::setSpaceCutArrow(float value)
{
    _SpaceCutArrow = value;
    emit spaceCutArrowChanged();
}

void GanttView::setTask(QVariantList task)
{
    _Tasks.clear();
    for (QVariant var : task)
    {
        TaskData *task = qobject_cast<TaskData*>(var.value<TaskData*>());
        if (task == nullptr)
            continue;
        _Tasks.push_back(task);
    }
}

void GanttView::setSizeTaskBar(float value)
{
    _SizeTaskSlideBar = value;
    emit sizeTaskBarChanged();
}

Qt::CursorShape GanttView::refreshTypeAction(QPointF mousePos, bool isLeftClick)
{
    if (!isLeftClick)
    {
        QDate currentDateCursor = _TodayDate;
        int diff = (int)(_CursorX / _SizeX);
        currentDateCursor = currentDateCursor.addDays(-diff);
        float currentY = _SizeYTop * 3;
        float baseY;
        if (_SizeX <= _MinSizeWeek)
        {
            currentY -= _SizeYTop;
        }
        if (_SizeX <= _MinSizeYear)
        {
            currentY -= _SizeYTop;
        }
        baseY = currentY;
        QRectF maxRect = this->boundingRect();
        float currentDrawX = (maxRect.center().x() - (_SizeX / 2.0f)) + (_CursorX - (float)(diff * _SizeX));
        _Type = NONE;
        for (TaskData *task : _Tasks)
        {
            int diffStart = task->startDate().daysTo(QDateTime(currentDateCursor, task->startDate().time()));
            if (task->isMilestone())
            {
                float center = (_SizeX) / 2 - (_SizeY) / 2;
                QRectF rect(currentDrawX - (float)diffStart * _SizeX + center, currentY, _SizeY, _SizeY);
                if (rect.contains(mousePos))
                {
                    _Type = SET_DEPENDANCE;
                    _SelectedTaskData = task;
                    break;
                }
            }
            else
            {
                int length = task->startDate().daysTo(task->dueDate()) + 1;
                QRectF rect(currentDrawX - (float)diffStart * _SizeX, currentY, (float)length * _SizeX, _SizeY);
                if (rect.contains(mousePos))
                {
                    if (qFabs(mousePos.x() - rect.left()) < _SpaceTask * 2)
                    {
                        _Type = SET_PROGRESSION;
                        _SelectedTaskData = task;
                        break;
                    }
                    else if (qFabs(mousePos.x() - (rect.right())) < _SpaceTask * 2)
                    {
                        _Type = CHANGE_DEADLINE;
                        _SelectedTaskData = task;
                        break;
                    }
                    else
                    {
                        _Type = SET_DEPENDANCE;
                        _SelectedTaskData = task;
                        break;
                    }
                }
            }
            currentY += _SizeY;
        }
        if (_Type == NONE)
        {
            maxRect.setTop(baseY);
            maxRect.setLeft(maxRect.left() + _SizeTaskSlideBar);
            if (maxRect.contains(mousePos))
                _Type = MOVE;
            maxRect.setLeft(maxRect.left() - _SizeTaskSlideBar);
        }
        int currentTask = 0;
        for (TaskData *task : _Tasks)
        {
            QRectF taskRect = QRectF(20, baseY + currentTask * _SizeY, _SizeTaskSlideBar - 20, _SizeY - 3);
            if (taskRect.contains(mousePos))
            {
                _Type = CLIC;
                _SelectedTaskData = task;
                break;
            }
            currentTask++;
        }
        if (maxRect.contains(mousePos) && qFabs(mousePos.x() - (maxRect.left() + _SizeTaskSlideBar)) < _SpaceTask * 2)
        {
            _Type = EXPAND;
            _SelectedTaskData = nullptr;
        }
    }
    Qt::CursorShape cursorS;
    switch (_Type)
    {
    case NONE:
        cursorS = Qt::ArrowCursor;
        break;
    case MOVE:
        cursorS = ((isLeftClick) ? Qt::ClosedHandCursor : Qt::OpenHandCursor);
        break;
    case CLIC:
    case SET_DEPENDANCE:
        cursorS = Qt::PointingHandCursor;
        break;
    case SET_PROGRESSION:
        cursorS = Qt::SplitHCursor;
        break;
    case CHANGE_DEADLINE:
    case EXPAND:
        cursorS = Qt::SizeHorCursor;
        break;
    default:
        cursorS = Qt::ArrowCursor;
        break;
    }
    emit moveTypeChanged();
    return cursorS;
}

void GanttView::onMove(QPointF mousePos)
{
    if (_LastMousePos.x() < 0 || _LastMousePos.y() < 0)
        return;
    switch (_Type)
    {
    case MOVE:
        setCursorX(_CursorX + (mousePos.x() - _LastMousePos.x()));
        break;
    case SET_DEPENDANCE:
        _LastMousePos = mousePos;
        update();
        break;
    case SET_PROGRESSION:
        SetProgression(mousePos);
        break;
    case CHANGE_DEADLINE:
        SetDeadline(mousePos);
        break;
    case EXPAND:
        if (_SelectedTaskData == nullptr)
        {
            _SizeTaskSlideBar = mousePos.x() - boundingRect().left();
            if (_SizeTaskSlideBar < 50)
                _SizeTaskSlideBar = 50;
            if (_SizeTaskSlideBar > boundingRect().width() - 100)
                _SizeTaskSlideBar = boundingRect().width() - 100;
            update();
            emit sizeTaskBarChanged();
        }
        else
        {
            // Si on etend une tache
        }
        break;
    }
    _LastMousePos = mousePos;
}

void GanttView::onClic(QPointF mousePos)
{
    _LastMousePos = mousePos;
    _StartMousePos = mousePos;
    if (_Type == CLIC)
    {

    }
}

void GanttView::onRelease(QPointF mousePos)
{
    switch (_Type)
    {
    case SET_DEPENDANCE:
        SetDependance(mousePos);
        qDebug() << "Register data for dependances";
        break;
    case SET_PROGRESSION:
        qDebug() << "Register data for progression";
        break;
    case CHANGE_DEADLINE:
        qDebug() << "Register data for deadlines";
        break;
    }

    _LastMousePos = QPointF(-1, -1);
    _StartMousePos = _LastMousePos;
    refreshTypeAction(mousePos, false);
    update();
}

void GanttView::onDoubleClic(QPointF mousePos)
{
    _LastMousePos = QPointF(-1, -1);
}

float GanttView::sizeX() const
{
    return _SizeX;
}

float GanttView::sizeY() const
{
    return _SizeY;
}

float GanttView::sizeYTop() const
{
    return _SizeYTop;
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

float GanttView::spaceTask() const
{
    return _SpaceTask;
}

float GanttView::spaceCutArrow() const
{
    return _SpaceCutArrow;
}

GanttView::MovementType GanttView::moveType() const
{
    return _Type;
}

float GanttView::sizeTaskBar() const
{
    return _SizeTaskSlideBar;
}

QQmlListProperty<TaskData> GanttView::tasks()
{
    return (QQmlListProperty<TaskData>(this, _Tasks));
}
