/**
 * @param array{{
 * user_id:         int,
 * user_name:       string,
 * target_x:        int,
 * target_y:        int,
 * target_map_id:   int,
 * rank_name:       string,
 * rank_num:            int,
 * village_icon:    string,
 * alignment:       string,
 * attack:          boolean,
 * attack_id:       string,
 * level:           int,
 * battle_id:       int,
 * direction:       string,
 * invulnerable:    boolean,
 * }} player
 */
export const ScoutArea = ({
    mapData,
    scoutData,
    membersLink,
    attackPlayer,
    sparPlayer,
    ranksToView,
    playerId,
}) => {
    return (
        <div className='travel-scout-container'>
            <div className='travel-scout'>
                {(mapData) && scoutData
                    .filter(user => ranksToView[parseInt(user.rank_num)] === true)
                    .map((player_data) => (
                        (player_data.user_id != playerId) &&
                        <Player
                            key={player_data.user_id}
                            player_data={player_data}
                            membersLink={membersLink}
                            attackPlayer={attackPlayer}
                            sparPlayer={sparPlayer}
                            colosseumCoords={mapData.colosseum_coords}
                        />
                    )
                )}
            </div>
        </div>
    );
};

export const QuickScout = ({
    mapData,
    scoutData,
    attackPlayer,
    sparPlayer,
    ranksToView,
    playerId,
    travelActionRef,
}) => {
    return (
        <div className='quick-scout-contrainer' style={{
            position: 'absolute',
            height: '75px',
            width: '75px',
            top: '50%',
            left: '50%',
            transform: 'translate(-50%, calc(-50% - 1px))',
            cursor: 'pointer',
            zIndex: 10,
            background: 'linear-gradient(var(--sidebar-button-background-color), var(--sidebar-button-background-color))',
            backgroundSize: "cover",
            backgroundRepeat: "no-repeat",
            backgroundPosition: "center",
            boxShadow: '0 0 0 1px var(--sidebar-button-border-color) inset',
            userSelect: 'none',
            touchAction: 'none',
            }}>
            {scoutData && scoutData.find(user => ranksToView[parseInt(user.rank_num)] === true && user.user_id !== playerId) && (
                <QuickScoutInner
                    key={scoutData[0].user_id}
                    player_data={scoutData.find(user => ranksToView[parseInt(user.rank_num)] === true && user.user_id !== playerId)}
                    attackPlayer={attackPlayer}
                    sparPlayer={sparPlayer}
                    colosseumCoords={mapData ? mapData.colosseum_coords : null}
                    travelActionRef={travelActionRef}
                />
            )}
        </div>
    );
};

const Player = ({
    player_data,
    membersLink,
    attackPlayer,
    sparPlayer,
    colosseumCoords,
}) => {
    return (
        <div key={player_data.user_id}
             className={alignmentClass(player_data.alignment)}>
            <div className={'travel-scout-name' + " " + visibilityClass(player_data.invulnerable)}>
                <a href={membersLink + '&user=' + player_data.user_name}>
                    {player_data.user_name}
                </a>
                <span>Lv.{player_data.level}</span>
            </div>
            <div className='travel-scout-location'>
                {player_data.target_x} &#8729; {player_data.target_y}
            </div>
            <div className='travel-scout-faction'>
                <img src={'./' + player_data.village_icon} alt='mist' />
            </div>
            <div className='travel-scout-attack'>
                {(player_data.attack === true && parseInt(player_data.battle_id, 10) === 0 && !player_data.invulnerable) && (
                    (player_data.target_x === colosseumCoords.x && player_data.target_y === colosseumCoords.y) ?
                        <a onClick={() => sparPlayer(player_data.user_id)}></a> :
                        <a onClick={() => attackPlayer(player_data.attack_id)}></a>
                )}
                {(player_data.attack === true && parseInt(player_data.battle_id, 10) > 0) && (
                    <span className='in-battle'></span>
                )}
                {(player_data.attack === false && player_data.direction !== 'none') && (
                    <span className={`direction ${player_data.direction}`}></span>
                )}
            </div>
        </div>
    );
}

const QuickScoutInner = ({
    player_data,
    attackPlayer,
    sparPlayer,
    colosseumCoords,
    travelActionRef,
}) => {
    const quickScoutMouseDown = () => {
        switch (player_data.direction) {
            case 'north':
                event = new KeyboardEvent('keydown', {
                    key: 'w', 
                    bubbles: true,
                    cancelable: true,
                });
                travelActionRef.current.dispatchEvent(event);
                break;
            case 'northeast':
                event = new KeyboardEvent('keydown', {
                    key: 'w',
                    bubbles: true,
                    cancelable: true,
                });
                travelActionRef.current.dispatchEvent(event);
                event = new KeyboardEvent('keydown', {
                    key: 'd',
                    bubbles: true,
                    cancelable: true,
                });
                travelActionRef.current.dispatchEvent(event);
                break;
            case 'east':
                event = new KeyboardEvent('keydown', {
                    key: 'd',
                    bubbles: true,
                    cancelable: true,
                });
                travelActionRef.current.dispatchEvent(event);
                break;
            case 'southeast':
                event = new KeyboardEvent('keydown', {
                    key: 'd',
                    bubbles: true,
                    cancelable: true,
                });
                travelActionRef.current.dispatchEvent(event);
                event = new KeyboardEvent('keydown', {
                    key: 's',
                    bubbles: true,
                    cancelable: true,
                });
                travelActionRef.current.dispatchEvent(event);
                break;
            case 'south':
                event = new KeyboardEvent('keydown', {
                    key: 's',
                    bubbles: true,
                    cancelable: true,
                });
                travelActionRef.current.dispatchEvent(event);
                break;
            case 'southwest':
                event = new KeyboardEvent('keydown', {
                    key: 's',
                    bubbles: true,
                    cancelable: true,
                });
                travelActionRef.current.dispatchEvent(event);
                event = new KeyboardEvent('keydown', {
                    key: 'a',
                    bubbles: true,
                    cancelable: true,
                });
                travelActionRef.current.dispatchEvent(event);
                break;
            case 'west':
                event = new KeyboardEvent('keydown', {
                    key: 'a',
                    bubbles: true,
                    cancelable: true,
                });
                travelActionRef.current.dispatchEvent(event);
                break;
            case 'northwest':
                event = new KeyboardEvent('keydown', {
                    key: 'a',
                    bubbles: true,
                    cancelable: true,
                });
                travelActionRef.current.dispatchEvent(event);
                event = new KeyboardEvent('keydown', {
                    key: 'w',
                    bubbles: true,
                    cancelable: true,
                });
                travelActionRef.current.dispatchEvent(event);
                break;
            default:
                break;
        }
    };
    const quickScoutMouseUp = () => {
        event = new KeyboardEvent('keyup', {
            key: 'w',
            bubbles: true,
            cancelable: true,
        });
        travelActionRef.current.dispatchEvent(event);
        event = new KeyboardEvent('keyup', {
            key: 'a',
            bubbles: true,
            cancelable: true,
        });
        travelActionRef.current.dispatchEvent(event);
        event = new KeyboardEvent('keyup', {
            key: 's',
            bubbles: true,
            cancelable: true,
        });
        travelActionRef.current.dispatchEvent(event);
        event = new KeyboardEvent('keyup', {
            key: 'd',
            bubbles: true,
            cancelable: true,
        });
        travelActionRef.current.dispatchEvent(event);
    };

    return (
        <div className='quick-scout' onMouseDown={quickScoutMouseDown} onMouseUp={quickScoutMouseUp}>
            {(player_data.attack === true && parseInt(player_data.battle_id, 10) === 0 && !player_data.invulnerable) && (
                (player_data.target_x === colosseumCoords.x && player_data.target_y === colosseumCoords.y) ?
                    <a onClick={() => sparPlayer(player_data.user_id)}></a> :
                    <a onClick={() => attackPlayer(player_data.attack_id)}></a>
            )}
            {(player_data.attack === true && parseInt(player_data.battle_id, 10) > 0) && (
                <span className='in-battle'></span>
            )}
            {(player_data.attack === false && player_data.direction !== 'none') && (
                <span className={`direction ${player_data.direction}`}></span>
            )}
        </div>
    );
}

const visibilityClass = (invulnerable) => {
    if (invulnerable) {
        return 'invulnerable';
    }
    return ' ';
}

const alignmentClass = (alignment) => {
    let class_name = 'travel-scout-entry travel-scout-';
    switch (alignment) {
        case 'Ally':
            class_name += 'ally';
            break;
        case 'Enemy':
            class_name += 'enemy';
            break;
        case 'Neutral':
            class_name += 'neutral';
            break;
    }
    return class_name;
}