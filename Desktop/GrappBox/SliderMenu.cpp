#include <QException>
#include <QDebug>
#include <QMessageBox>
#include <QJsonDocument>
#include <QJsonObject>
#include <QJSonArray>
#include <QJsonValueRef>
#include "API/SDataManager.h"
#include "SliderMenu.h"

SliderMenu::SliderMenu(QWidget *parent) : QWidget(parent)
{
    _MainLayout = new QVBoxLayout();

    _LabelComboBox = new QLabel("Select a project");
    _LabelComboBox->setFixedHeight(24);
    _ComboBoxProject = new QComboBox();
    _ComboBoxProject->setFixedHeight(24);

    _MainLayout->addWidget(_LabelComboBox);
    _MainLayout->addWidget(_ComboBoxProject);

    _MainLayout->setAlignment(Qt::AlignTop);
    setLayout(_MainLayout);
    _MainLayout->setMargin(0);
    _MainLayout->setSpacing(0);
    _CurrentIndex = 0;

    QObject::connect(_ComboBoxProject, SIGNAL(currentIndexChanged(int)), this, SLOT(OnProjectChange(int)));
}

void SliderMenu::UpdateProject()
{
    QVector<QString> data;
    data.append(API::SDataManager::GetDataManager()->GetToken());
    API::SDataManager::GetCurrentDataConnector()->Get(API::DP_PROJECT, API::GR_LIST_PROJECT, data, this, "ProjectLoaded", "ProjectFailLoad");
    _ComboBoxProject->setDisabled(true);
}

void SliderMenu::ProjectLoaded(int id, QByteArray data)
{
    QJsonDocument doc = QJsonDocument::fromJson(data);
    QJsonObject objMain = doc.object();
    _ComboBoxProject->clear();
    _ComboBoxProject->addItem("No project selected", -1);
    for (QJsonValueRef ref : objMain["data"].toObject()["array"].toArray())
    {
        QJsonObject obj = ref.toObject();
        _ComboBoxProject->addItem(obj["project_name"].toString(), obj["project_id"].toInt());
    }
    int currentProject = API::SDataManager::GetDataManager()->GetCurrentProject();
    if (currentProject != -1)
    {
        for (int i = 0; i < _ComboBoxProject->count(); ++i)
        {
            if (i != _ComboBoxProject->currentIndex() && _ComboBoxProject->itemData(i).toInt() == currentProject)
            {
                _ComboBoxProject->setCurrentIndex(i);
                break;
            }
        }
    }
    UpdateButtonDisable();
    _ComboBoxProject->setDisabled(false);
}

void SliderMenu::ProjectFailLoad(int id, QByteArray data)
{
    QMessageBox::critical(this, "Networ", "Impossible to retrieve project. Please contact an administrator.");
}

void SliderMenu::OnProjectChange(int id)
{
    API::SDataManager::GetDataManager()->SetCurrentProjectId(_ComboBoxProject->itemData(id).toInt());
    emit ProjectChange();
    UpdateButtonDisable();
}

void SliderMenu::AddMenuItem(QString name, int id, bool hided, bool needProject)
{
    QPushButton *newItem = new QPushButton(name);
    newItem->setMaximumHeight(40);
	newItem->setObjectName("menu");
    newItem->setSizePolicy(QSizePolicy::Expanding, QSizePolicy::Expanding);
    if (hided)
        newItem->hide();
    _ListButton[id] = newItem;
    _NeedProjectButton[id] = needProject;

    _MainLayout->addWidget(newItem);
    UpdateButtonDisable();
    QObject::connect(newItem, SIGNAL(clicked(bool)), this, SLOT(ButtonChangeMenu()));
}

QString SliderMenu::GetMenuItem(int id)
{
    if (!_ListButton.contains(id))
        throw new QException();
    return _ListButton[id]->text();
}

void SliderMenu::ForceChangeMenu(int menu)
{
    qDebug() << "Menu = " << menu;
    if (!_ListButton.contains(menu))
        throw QString("Force change menu");
    _CurrentIndex = menu;
    for (QMap<int, QPushButton*>::iterator it = _ListButton.begin(); it != _ListButton.end(); ++it)
    {
		(it.value())->setObjectName("menu");
    }
	_ListButton[menu]->setObjectName("menu-selected");
}

void SliderMenu::ButtonChangeMenu()
{
    QObject *obj = QObject::sender();
    int index = _ListButton.key(dynamic_cast<QPushButton*>(obj));
    qDebug() << "Index button change menu : " << index;
    ForceChangeMenu(index);
    emit MenuChanged(index);
}

void SliderMenu::UpdateButtonDisable()
{
    int currentProject = API::SDataManager::GetDataManager()->GetCurrentProject();
    for (int item : _ListButton.keys())
    {
        if (_NeedProjectButton[item] && currentProject == -1)
            _ListButton[item]->setDisabled(true);
        else
            _ListButton[item]->setDisabled(false);
    }
}
