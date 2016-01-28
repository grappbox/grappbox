#include "whiteboardcanvas.h"
#include <QDebug>

WhiteboardCanvas::WhiteboardCanvas(QWidget *parent) : QGraphicsScene(parent)
{
	_CurrentType = GT_RECT;
	_CurrentLine = NULL;
	_CurrentEllipse = NULL;
	_CurrentRect = NULL;
	_CurrentDiamond = NULL;
	_CurrentHandWriting = NULL;
	_BrushColor = QColor(0, 0, 0);
	_BrushWidth = 5;
	_BackgroundColor = QColor(128, 100, 0, 255);
}

void WhiteboardCanvas::mousePressEvent(QGraphicsSceneMouseEvent *event)
{
	if (event->button() != Qt::LeftButton)
	{
		if (event->button() == Qt::RightButton)
			_MouseRightClickPoint = event->scenePos();
		DeleteCurrentObjectDraw();
		return;
	}
	QPointF mousePos = event->scenePos();
	QPen pen(QPen(QBrush(_BrushColor), _BrushWidth));
	QBrush backgroud(_BackgroundColor);
	switch (_CurrentType)
	{
	case GT_LINE:
		_CurrentLine = addLine(mousePos.x(), mousePos.y(), mousePos.x(), mousePos.y(), pen);
		break;
	case GT_RECT:
		_CurrentRect = addRect(mousePos.x(), mousePos.y(), 1, 1, pen, backgroud);
		_StartPoint = mousePos;
		break;
	case GT_CIRCLE:
		_CurrentEllipse = addEllipse(mousePos.x(), mousePos.y(), 1, 1, pen, backgroud);
		_StartPoint = mousePos;
		break;
	case GT_LOZENGE:
		_CurrentDiamond = new CustomGraphicsDiamondItem(mousePos.x(), mousePos.y(), 50, 50, pen, backgroud);
		addItem(_CurrentDiamond);
		_StartPoint = mousePos;
		break;
	case GT_HAND_WRITE:
		_CurrentHandWriting = new CustomGraphicsHandWriteItem(pen);
		addItem(_CurrentHandWriting);
		break;
	case GT_ERASER:
		break;
	}
}

void WhiteboardCanvas::DeleteCurrentObjectDraw()
{
	if (_CurrentLine)
		this->removeItem(_CurrentLine);
	if (_CurrentEllipse)
		this->removeItem(_CurrentEllipse);
	if (_CurrentRect)
		this->removeItem(_CurrentRect);
	if (_CurrentDiamond)
		this->removeItem(_CurrentDiamond);
	if (_CurrentHandWriting)
		this->removeItem(_CurrentHandWriting);
}

void WhiteboardCanvas::mouseMoveEvent(QGraphicsSceneMouseEvent *event)
{
	if (_CurrentLine)
	{
		_CurrentLine->setLine(_CurrentLine->line().x1(),
			_CurrentLine->line().y1(),
			event->scenePos().x(),
			event->scenePos().y());
	}
	if (_CurrentEllipse)
	{
		qreal x, y, width, height;
		x = _StartPoint.x();
		y = _StartPoint.y();
		width = event->scenePos().x() - x;
		height = event->scenePos().y() - y;
		_CurrentEllipse->setRect(x, y, width, height);
	}
	if (_CurrentRect)
	{
		qreal x, y, width, height;
		x = _StartPoint.x();
		y = _StartPoint.y();
		width = event->scenePos().x() - x;
		height = event->scenePos().y() - y;
		if (width < 0)
		{
			x += width;
			width = -width;
		}
		if (height < 0)
		{
			y += height;
			height = -height;
		}
		_CurrentRect->setRect(x, y, width, height);
	}
	if (_CurrentDiamond)
	{
		qreal x, y, width, height;
		x = _StartPoint.x();
		y = _StartPoint.y();
		width = event->scenePos().x() - x;
		height = event->scenePos().y() - y;
		if (width < 0)
		{
			x += width;
			width = -width;
		}
		if (height < 0)
		{
			y += height;
			height = -height;
		}
		_CurrentDiamond->SetRect(x, y, width, height);
	}
	if (_CurrentHandWriting)
	{
		_CurrentHandWriting->Append(event->scenePos().x(), event->scenePos().y());
	}
	this->update();
}

void WhiteboardCanvas::mouseReleaseEvent(QGraphicsSceneMouseEvent *event)
{
	if (_CurrentLine)
	{
		_ItemStacked.append(_CurrentLine);
		//Send here to API
	}
	if (_CurrentEllipse)
	{
		_ItemStacked.append(_CurrentLine);
		//Send here to API
	}
	if (_CurrentRect)
	{
		_ItemStacked.append(_CurrentRect);
		//Send here to API
	}
	if (_CurrentDiamond)
	{
		_ItemStacked.append(_CurrentDiamond);
		//Send here to API
	}
	if (_CurrentHandWriting)
	{
		_ItemStacked.append(_CurrentHandWriting);
		//Send here to API
	}
	_CurrentLine = NULL;
	_CurrentEllipse = NULL;
	_CurrentRect = NULL;
	_CurrentDiamond = NULL;
	_CurrentHandWriting = NULL;
}

void WhiteboardCanvas::drawBackground(QPainter *painter, const QRectF &rect)
{
	const int gridSize = 25;

	qreal left = int(rect.left()) - (int(rect.left()) % gridSize);
	qreal top = int(rect.top()) - (int(rect.top()) % gridSize);

	QVarLengthArray<QLineF, 100> lines;

	for (qreal x = left; x < rect.right(); x += gridSize)
		lines.append(QLineF(x, rect.top(), x, rect.bottom()));
	for (qreal y = top; y < rect.bottom(); y += gridSize)
		lines.append(QLineF(rect.left(), y, rect.right(), y));
	painter->setOpacity(0.3);
	painter->drawLines(lines.data(), lines.size());
}

void WhiteboardCanvas::SetGraphicsType(GraphicsType type)
{
	if (type == GT_NONE)
		return;
	if (type == GT_TEXT)
	{
		_Popup = new BodyWhiteboardWritingText(dynamic_cast<QWidget*>(this->parent()));
		_Popup->show();
		connect(_Popup, SIGNAL(Accept(QString, bool, bool, int)), this, SLOT(OnTextPopupAdd(QString, bool, bool, int)));
		connect(_Popup, SIGNAL(Cancel()), this, SLOT(OnTextPopupCancel()));
	}
	if (type != _CurrentType)
		DeleteCurrentObjectDraw();
	if (type != GT_TEXT)
		_CurrentType = type;
}

void WhiteboardCanvas::SetBrushColor(const QColor &col)
{
	_BrushColor = col;
}

void WhiteboardCanvas::SetBrushWidth(qreal width)
{
	_BrushWidth = width;
}

void WhiteboardCanvas::SetBackgroundColor(const QColor &col)
{
	_BackgroundColor = col;
}

void WhiteboardCanvas::OnTextPopupCancel()
{
	_Popup->hide();
	delete _Popup;
}

void WhiteboardCanvas::OnTextPopupAdd(QString str, bool italic, bool bold, int size)
{
	QGraphicsTextItem *text = new QGraphicsTextItem(str);
	QFont font = text->font();
	font.setItalic(italic);
	font.setBold(bold);
	font.setPixelSize(size);
	text->setFont(font);
	text->setPos(_MouseRightClickPoint - QPointF(text->document()->size().width() / 2, text->document()->size().height() / 2));
	this->addItem(text);
	_ItemStacked.append(text);
	//Send here to API
	OnTextPopupCancel();
}
