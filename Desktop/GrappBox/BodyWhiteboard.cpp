#include <QDebug>
#include <QStandardItem>
#include <QtWidgets/QHeaderView>
#include <QStandardItemModel>
#include <QFile>
#include <QXmlStreamReader>
#include <QtWidgets/QMessageBox>
#include <QPainter>
#include <QPair>
#include <QMap>
#include <QtWidgets/QStackedLayout>
#include <QJsonDocument>
#include <QJsonObject>
#include <QJsonArray>
#include "SDataManager.h"
#include "utils.h"
#include "BodyWhiteboard.h"
#include "SStyleLoader.h"

BodyWhiteboard::BodyWhiteboard(QWidget *parent) : QWidget(parent)
{
    _MapId[0] = GT_LINE;
    _MapId[1] = GT_RECT;
    _MapId[2] = GT_CIRCLE;
    _MapId[3] = GT_LOZENGE;
    _MapId[4] = GT_NONE;
    _MapId[5] = GT_HAND_WRITE;
    _MapId[6] = GT_TEXT;
    _MapId[7] = GT_NONE;
    _MapId[8] = GT_ERASER;

    _MainLayout = new QStackedLayout();
    _View = new WhiteboardGraphicsView();
    _Whiteboard = new WhiteboardCanvas();
    _MainLayoutWhiteboard = new QVBoxLayout();
	_WhiteboardChoice = new FlowLayout();
    _MenuLayout = new QHBoxLayout();

	_WhiteboardChoice->setContentsMargins(5, 5, 5, 5);
	_WhiteboardChoice->setSpacing(5);

	_CreateWhiteboard = new QPushButton("Create");
	_CreateWhiteboard->setObjectName("create");
	_CreateWhiteboard->setFixedSize(90, 30);

    InitializeComboBox();

    _MenuLayout->addWidget(_PenSizeChoice);
    _MenuLayout->addWidget(_ColorPenChoice);
    _MenuLayout->addWidget(_ColorBackgroundChoice);
    _MainLayoutWhiteboard->addLayout(_MenuLayout);
    _MainLayoutWhiteboard->addWidget(_View);
    QWidget *whiteboardFrame = new QWidget;
    whiteboardFrame->setLayout(_MainLayoutWhiteboard);
	QWidget *whiteboardChoiceFrame = new QWidget;
	QVBoxLayout *choiceMainLayout = new QVBoxLayout();
	QHBoxLayout *mainBar = new QHBoxLayout();
	QLabel *choiceTitle = new QLabel("Choose a whiteboard or create one");
	choiceTitle->setFixedHeight(50);
	choiceTitle->setObjectName("title");
	choiceTitle->setAlignment(Qt::AlignLeft);
	mainBar->addWidget(choiceTitle);
	mainBar->addWidget(_CreateWhiteboard);
	mainBar->setAlignment(_CreateWhiteboard, Qt::AlignRight);
	mainBar->setContentsMargins(0, 0, 0, 0);
	mainBar->setSpacing(0);
	choiceMainLayout->setAlignment(Qt::AlignTop | Qt::AlignJustify);
	choiceMainLayout->setContentsMargins(0, 0, 0, 0);
	choiceMainLayout->setSpacing(0);
	choiceMainLayout->addLayout(mainBar);
	choiceMainLayout->addLayout(_WhiteboardChoice);
	whiteboardChoiceFrame->setLayout(choiceMainLayout);
    _MainLayout->addWidget(whiteboardChoiceFrame);
    _MainLayout->addWidget(whiteboardFrame);
    _View->setScene(_Whiteboard);

	setStyleSheet(SStyleLoader::LoadStyleSheet(WHITEBOARD));

    setLayout(_MainLayout);
    connect(_View, SIGNAL(OnMenuAction(int)), this, SLOT(OnActionWhiteboard(int)));
	connect(_CreateWhiteboard, SIGNAL(clicked(bool)), this, SLOT(OpenNewProjectPopup()));
}

void BodyWhiteboard::InitializeComboBox()
{
    QFile xmlFileColorPen(":/Configuration/Ressources/ConfigurationFiles/WhiteboardColors.xml");
    if (!xmlFileColorPen.open(QIODevice::ReadOnly | QIODevice::Text))
    {
        QMessageBox::critical(NULL, "Internal Error",
                              "Unable to open internal file for tools model.\n"
                              "Please refere to the support for more information."
                              "\nError message : Color_Pen_Choice_Open_xmlFileColorPen.", QMessageBox::Ok);
        return;
    }
    QXmlStreamReader xml(&xmlFileColorPen);
    while (!xml.atEnd())
    {
        xml.readNext();
        if (xml.name() == "Color" && xml.tokenType() == QXmlStreamReader::StartElement)
        {
            QString name = xml.attributes().value("name").toString();
            QString hexa = xml.attributes().value("hexa").toString();
            _HexaList.append(QPair<QString, QString>(name, hexa));
        }
    }

    InitializeColorPen();
    InitializeBackground();
    InitializePenWidth();
}

void BodyWhiteboard::InitializeColorPen()
{
    QImage alphaMap(":/Mask/Ressources/Mask/CircleMaskMemberPicture.png");

    _TableColorPen = new QTableView();
    _TableColorPen->setMinimumWidth(500);
    _TableColorPen->setSelectionMode(QAbstractItemView::SingleSelection);
    _TableColorPen->setSelectionBehavior(QAbstractItemView::SelectItems);
    _TableColorPen->horizontalHeader()->hide();
    _TableColorPen->verticalHeader()->hide();
    _ColorPenChoice = new QComboBox();
    _ColorPenChoice->setView(_TableColorPen);
    _ColorPenChoice->setModel(new QStandardItemModel(_ColorPenChoice));
    _ColorPenChoice->setFixedWidth(40);
    _ColorPenChoice->setFixedHeight(32);
    QStandardItemModel *model = dynamic_cast<QStandardItemModel*>(_ColorPenChoice->model());
    QList<QPair<QString, QString> >::iterator it = _HexaList.begin();
    for (int i = 0; i < 5; ++i)
    {
        QList<QStandardItem*> *newList = new QList<QStandardItem*>();
        for (int j = 0; j < 5; ++j)
        {
            if (it == _HexaList.end())
            {
                QMessageBox::critical(NULL, "Internal Error",
                                      "Unable to find all colors for the whiteboard.\n"
                                      "Error message : Color_Pen_Choice_Iteration_hexaList.", QMessageBox::Ok);
                return;
            }
            QImage icon(alphaMap);
            icon.fill(QColor(it->second));
            icon.setAlphaChannel(alphaMap);
            newList->append(new QStandardItem(QIcon(QPixmap::fromImage(icon)), it->first));
            ++it;
        }
        model->appendColumn(*newList);
    }
    _ColorPenChoice->setCurrentIndex(0);
    _Whiteboard->SetBrushColor(QColor(_HexaList.begin()->second));
    connect(_ColorPenChoice->view(),&QAbstractItemView::pressed, this, &BodyWhiteboard::OnColorPenChange);
}

void BodyWhiteboard::InitializeBackground()
{
    _TableBackgroud = new QTableView();
    _TableBackgroud->setMinimumWidth(500);
    _TableBackgroud->setSelectionMode(QAbstractItemView::SingleSelection);
    _TableBackgroud->setSelectionBehavior(QAbstractItemView::SelectItems);
    _TableBackgroud->horizontalHeader()->hide();
    _TableBackgroud->verticalHeader()->hide();
    _ColorBackgroundChoice = new QComboBox();
    _ColorBackgroundChoice->setView(_TableBackgroud);
    _ColorBackgroundChoice->setModel(new QStandardItemModel(_ColorBackgroundChoice));
    _ColorBackgroundChoice->setFixedWidth(40);
    _ColorBackgroundChoice->setFixedHeight(32);
    QStandardItemModel *model = dynamic_cast<QStandardItemModel*>(_ColorBackgroundChoice->model());
    QList<QPair<QString, QString> >::iterator it = _HexaList.begin();
    for (int i = 0; i < 5; ++i)
    {
        QList<QStandardItem*> *newList = new QList<QStandardItem*>();
        for (int j = 0; j < 5; ++j)
        {
            if (it == _HexaList.end())
            {
                QMessageBox::critical(NULL, "Internal Error",
                                      "Unable to find all colors for the whiteboard.\n"
                                      "Error message : Color_Pen_Choice_Iteration_hexaList.", QMessageBox::Ok);
                return;
            }
            QImage icon(32, 32, QImage::Format_RGB32);
            icon.fill(QColor(it->second));
            newList->append(new QStandardItem(QIcon(QPixmap::fromImage(icon)), it->first));
            ++it;
        }
        model->appendColumn(*newList);
    }
    _ColorBackgroundChoice->setCurrentIndex(1);
    it = _HexaList.begin();
    ++it;
    _Whiteboard->SetBackgroundColor(QColor(it->second));
    connect(_ColorBackgroundChoice->view(),&QAbstractItemView::pressed, this, &BodyWhiteboard::OnColorBackgroudChange);
}

void BodyWhiteboard::InitializePenWidth()
{
    _PenSizeChoice = new QComboBox();
    for (int i = 0; i < 8; ++i)
    {
        float value = (i < 6) ? (float)i * 0.5f + 0.5f : i - 2;
        QImage icon(32, 32, QImage::Format_ARGB32);
        icon.fill(Qt::transparent);
        for (int x = 0; x < 32; ++x)
        {
            for (int y = 16 - value; y <= 16 + value; ++y)
            {
                icon.setPixel(x, y, QColor(0, 0, 0, 255).rgb());
            }
        }
        _PenSizeChoice->addItem(QIcon(QPixmap::fromImage(icon)), QString("%0 pt").arg(value), QVariant(value));
    }
    _PenSizeChoice->setCurrentIndex(3);
    _Whiteboard->SetBrushWidth(2);
    connect(_PenSizeChoice, SIGNAL(currentIndexChanged(int)), this, SLOT(OnPenSizeChange(int)));
}

void BodyWhiteboard::Show(int ID, MainWindow *mainApp)
{
    _WhiteboardId = ID;
    _MainApplication = mainApp;
    emit OnLoadingDone(ID);
	QVector<QString> data;
	data.push_back(USER_TOKEN);
	data.push_back(TO_STRING(CURRENT_PROJECT));
	DATA_CONNECTOR->Get(API::DP_WHITEBOARD, API::GR_WHITEBOARD, data, this, "OnLoadedWhiteboardDone", "OnLoadedWhiteboardFail");
}

void BodyWhiteboard::Hide()
{
    hide();
}

void BodyWhiteboard::OnQuitWhiteboard()
{

}

void BodyWhiteboard::OnEditWhiteboard(int id)
{
    _MainLayout->setCurrentIndex(1);

}

void BodyWhiteboard::OnActionWhiteboard(int id)
{
    _Whiteboard->SetGraphicsType(_MapId[id]);
}

void BodyWhiteboard::OnColorPenChange()
{
    QModelIndex index = _ColorPenChoice->view()->currentIndex();
    _ColorPenChoice->setModelColumn(index.column());
    QString key = _ColorPenChoice->view()->currentIndex().data().toString();
    for (QList<QPair<QString, QString> >::iterator it = _HexaList.begin(); it != _HexaList.end(); ++it)
    {
        if (it->first == key)
        {
            _Whiteboard->SetBrushColor(QColor(it->second));
            _Whiteboard->SetBackgroundColor(QColor(it->second));
            return ;
        }
    }
}

void BodyWhiteboard::OnColorBackgroudChange()
{
    QModelIndex index = _ColorBackgroundChoice->view()->currentIndex();
    _ColorBackgroundChoice->setModelColumn(index.column());
    QString key = _ColorBackgroundChoice->view()->currentIndex().data().toString();
    for (QList<QPair<QString, QString> >::iterator it = _HexaList.begin(); it != _HexaList.end(); ++it)
    {
        if (it->first == key)
        {
            _Whiteboard->SetBackgroundColor(QColor(it->second));
            return ;
        }
    }
}

void BodyWhiteboard::OnPenSizeChange(int index)
{
    float value = (index < 6) ? (float)index * 0.5f : index - 2;
    _Whiteboard->SetBrushWidth(value);
}

void BodyWhiteboard::OnLoadedWhiteboardDone(int, QByteArray data)
{
	while (QLayoutItem *item = _WhiteboardChoice->takeAt(0))
	{
		if (item->widget())
			delete item->widget();
		delete item;
	}
	QJsonDocument doc = QJsonDocument::fromJson(data);
	//QJsonArray array = doc.object()["data"].toObject()["array"].toArray();
	QJsonArray array = doc.object()["data"].toArray(); // REPLACE THIS BY THE COMMENT ON TOP
	for (QJsonValueRef ref : array)
	{
		QJsonObject obj = ref.toObject();
		Whiteboard w;
		w.id = obj["id"].toInt();
		w.projectId = CURRENT_PROJECT;
		w.name = obj["name"].toString();
		w.lastModification = QDateTime::fromString(obj["updatedAt"].toObject()["date"].toString(), FORMAT_DATE);
		w.creation = QDateTime::fromString(obj["createdAt"].toObject()["date"].toString(), FORMAT_DATE);
		WhiteboardButtonChoice *newChoice = new WhiteboardButtonChoice(w);
		_WhiteboardChoice->addWidget(newChoice);
	}
}

void BodyWhiteboard::OnLoadedWhiteboardFail(int, QByteArray data)
{
}

void BodyWhiteboard::NewProjectDone()
{
	QVector<QString> data;
	data.push_back(USER_TOKEN);
	data.push_back(TO_STRING(CURRENT_PROJECT));
	DATA_CONNECTOR->Get(API::DP_WHITEBOARD, API::GR_WHITEBOARD, data, this, "OnLoadedWhiteboardDone", "OnLoadedWhiteboardFail");
}

void BodyWhiteboard::OpenNewProjectPopup()
{
	CreateWhiteboardDialog *dial = new CreateWhiteboardDialog(this);
	connect(dial, SIGNAL(NewWhiteboard()), this, SLOT(NewProjectDone()));
	dial->exec();
	delete dial;
}
